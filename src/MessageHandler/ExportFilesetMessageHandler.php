<?php

namespace App\MessageHandler;

use App\Entity\Fileset;
use App\Message\ExportFilesetMessage;
use App\Repository\FileRepository;
use App\Repository\FilesetRepository;
use App\Util\MailSender;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Mime\Address;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportFilesetMessageHandler
{
    private const COPY_MAX_RETRIES = 3;
    private const COPY_RETRY_DELAY_SECONDS = 2;

    /**
     * ExportFilesetMessageHandler constructor.
     */
    public function __construct(
        private readonly FilesetRepository $filesetRepository,
        private readonly FileRepository $fileRepository,
        private readonly LoggerInterface $logger,
        private readonly MailSender $mailer,
        private readonly TwigEnvironment $twig,
        private readonly LockFactory $lockFactory,
        private readonly string $exportPath,
        private readonly string $datastorePath
    ) {
    }

    /**
     * Invoke function to process export fileset message.
     */
    public function __invoke(ExportFilesetMessage $exportFilesetMessage)
    {
        $exportFilesetMessageId = $exportFilesetMessage->getFilesetId();
        $exportUserEmail = $exportFilesetMessage->getExportUserEmail();

        $fileset = $this->filesetRepository->find($exportFilesetMessageId);
        if (!$fileset instanceof Fileset) {
            $this->logger->error(sprintf('Cannot find fileset with ID: "%d"', $exportFilesetMessageId));
            return;
        }

        $datasetUdi = $fileset->getDatasetSubmission()->getDataset()->getUdi();

        $lock = $this->lockFactory->createLock('export_fileset_' . $exportFilesetMessageId, ttl: 3600, autoRelease: true);

        if (!$lock->acquire()) {
            $this->logger->warning(sprintf(
                'Export for fileset ID %d (UDI: %s) is already in progress; skipping duplicate run.',
                $exportFilesetMessageId,
                $datasetUdi
            ));
            return;
        }

        try {
            $this->logger->info('Processing ExportFilesetMessage for ID: ' . $exportFilesetMessageId . ' associated with UDI: ' . $datasetUdi);
            $this->exportFiles($fileset, $datasetUdi);
            $this->notifyUserExportReady(udi: $datasetUdi, email: $exportUserEmail);
        } finally {
            $lock->release();
        }
    }

    /**
     * Export the files associated with the fileset to the export path (NFS share).
     *
     * @param string $udi The UDI of the dataset associated with the fileset, used to create a unique directory for the export.
     *
     * @throws \RuntimeException If a file cannot be successfully copied after all retries.
     */
    private function exportFiles(Fileset $fileset, string $udi): void
    {
        $dotUdi = str_replace(':', '.', $udi);
        $fileIds = [];
        foreach ($fileset->getProcessedFiles() as $file) {
            $fileIds[] = $file->getId();
        }
        $filesInfo = $this->fileRepository->getFilePathNameAndPhysicalPath($fileIds);

        @mkdir($this->exportPath . '/' . $dotUdi, 0755, true);
        $destinationPath = $this->exportPath . '/' . $dotUdi;

        foreach ($filesInfo as $fileItemInfo) {
            $sourceFileName = basename($fileItemInfo['physicalFilePath']);
            $sourcePath = $this->datastorePath . DIRECTORY_SEPARATOR . dirname($fileItemInfo['physicalFilePath']);
            $source = $sourcePath . DIRECTORY_SEPARATOR . $sourceFileName;
            $targetFileName = basename($fileItemInfo['filePathName']);
            $targetPath = $destinationPath . DIRECTORY_SEPARATOR . dirname($fileItemInfo['filePathName']);
            $target = $targetPath . DIRECTORY_SEPARATOR . $targetFileName;

            if (!is_dir($targetPath)) {
                @mkdir($targetPath, 0755, true);
            }

            $this->copyWithVerification($source, $target);
        }
    }

    /**
     * Copy a file and verify it was written correctly, retrying on failure.
     *
     * @throws \RuntimeException If the file cannot be copied and verified after all retries.
     */
    private function copyWithVerification(string $source, string $target): void
    {
        $sourceSize = filesize($source);
        if ($sourceSize === false) {
            throw new \RuntimeException(sprintf('Cannot read source file: "%s"', $source));
        }

        $lastError = null;
        for ($attempt = 1; $attempt <= self::COPY_MAX_RETRIES; $attempt++) {
            if (@copy($source, $target)) {
                clearstatcache(true, $target);
                $targetSize = filesize($target);
                if ($targetSize !== false && $targetSize === $sourceSize) {
                    if ($attempt > 1) {
                        $this->logger->info(sprintf('Successfully copied "%s" to "%s" on attempt %d.', $source, $target, $attempt));
                    }
                    return;
                }
                $lastError = sprintf(
                    'Size mismatch after copy: source=%d bytes, target=%d bytes (attempt %d/%d)',
                    $sourceSize,
                    $targetSize !== false ? $targetSize : -1,
                    $attempt,
                    self::COPY_MAX_RETRIES
                );
            } else {
                $lastError = sprintf('copy() returned false (attempt %d/%d)', $attempt, self::COPY_MAX_RETRIES);
            }

            $this->logger->warning(sprintf(
                'Failed to copy "%s" to "%s": %s',
                $source,
                $target,
                $lastError
            ));

            if ($attempt < self::COPY_MAX_RETRIES) {
                sleep(self::COPY_RETRY_DELAY_SECONDS);
            }
        }

        throw new \RuntimeException(sprintf(
            'Failed to copy "%s" to "%s" after %d attempts. Last error: %s',
            $source,
            $target,
            self::COPY_MAX_RETRIES,
            $lastError
        ));
    }

    /**
     * Notify user that the export is ready to review on Mimir.
     */
    private function notifyUserExportReady(string $udi, string $email): void
    {
        $addresses = [new Address(address: $email, name:'Fileset Reviewer')];

        try {
            $template = $this->twig->load('Email/data-repository-managers.dataset-export-ready.email.twig');
            $this->mailer->sendEmailMessage(
                $template,
                [
                    'udi' => $udi
                ],
                $addresses,
            );
            $this->logger->info(
                sprintf(
                    'Export-ready email sent to %s for UDI %s.',
                    $email,
                    $udi
                )
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'Failed to send export-ready email to %s for UDI %s. Error: %s',
                    $email,
                    $udi,
                    $e->getMessage()
                )
            );
        }
    }
}
