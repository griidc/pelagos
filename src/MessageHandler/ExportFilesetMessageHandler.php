<?php

namespace App\MessageHandler;

use App\Entity\Fileset;
use App\Message\ExportFilesetMessage;
use App\Repository\FileRepository;
use App\Repository\FilesetRepository;
use App\Util\MailSender;
use Symfony\Component\Mime\Address;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportFilesetMessageHandler
{
    /**
     * ExportFilesetMessageHandler constructor.
     */
    public function __construct(
        private readonly FilesetRepository $filesetRepository,
        private readonly FileRepository $fileRepository,
        private readonly LoggerInterface $logger,
        private readonly MailSender $mailer,
        private readonly TwigEnvironment $twig,
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
        $this->logger->info('Processing ExportFilesetMessage for ID: ' . $exportFilesetMessageId . ' associated with UDI: ' . $datasetUdi);
        $this->exportFiles($fileset, $datasetUdi);
        $this->notifyUserExportReady(udi: $datasetUdi, email: $exportUserEmail);
    }

    /**
     * Export the files associated with the fileset to the export path (NFS share).
     *
     * @param string $udi The UDI of the dataset associated with the fileset, used to create a unique directory for the export.
     */
    private function exportFiles(Fileset $fileset, string $udi): void
    {
        // Replace colon in UDI with a period to create a more cross-platform valid directory name.
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
            copy($source, $target);
        }
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
