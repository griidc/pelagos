<?php

namespace App\MessageHandler;

use App\Entity\Fileset;
use App\Entity\Dataset;
use App\Entity\Account;
use App\Event\EntityEventDispatcher;
use App\Message\ExportFilesetMessage;
use App\Repository\DatasetRepository;
use App\Repository\FileRepository;
use App\Repository\FilesetRepository;
use App\Util\MailSender;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Twig\Environment as TwigEnvironment;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class ExportFilesetMessageHandler
{

    /**
     * ExportFilesetMessageHandler constructor.
     */
    public function __construct(
        private readonly DatasetRepository $datasetRepository,
        private readonly FilesetRepository $filesetRepository,
        private readonly FileRepository $fileRepository,
        private readonly LoggerInterface $logger,
        private readonly MailSender $mailer,
        private readonly TwigEnvironment $twig,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly string $exportPath,
        private readonly string $datastorePath
    ) {
    }

    /**
     * Invoke function to process export fileset message.
     *
     * @param ExportFilesetMessage $exportFilesetMessage the ExportFileset Message that has to be handled
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
        $this->exportFiles($fileset, $datasetUdi, $exportUserEmail);
    }

    private function exportFiles($fileset, $udi, $exportUserEmail): void
    {
        $nudi = str_replace(':', '.', $udi);
        $fileIds = [];
        foreach ($fileset->getProcessedFiles() as $file) {
            $fileIds[] = $file->getId();
        }
        $filesInfo = $this->fileRepository->getFilePathNameAndPhysicalPath($fileIds);

        @mkdir($this->exportPath . '/' . $nudi, 0755, true);
        $destinationPath = $this->exportPath . '/' . $nudi;

        foreach ($filesInfo as $fileItemInfo) {
            $sourceFileName = basename($fileItemInfo['physicalFilePath']);
            $sourcePath = $this->datastorePath . DIRECTORY_SEPARATOR . dirname($fileItemInfo['physicalFilePath']);
            $source = $sourcePath . DIRECTORY_SEPARATOR . $sourceFileName;
            $targetFileName = basename($fileItemInfo['filePathName']);
            $targetPath = $destinationPath . DIRECTORY_SEPARATOR . dirname($fileItemInfo['filePathName']);
            $target = $targetPath . DIRECTORY_SEPARATOR . $targetFileName;

            if (!is_dir($targetPath)) {
                @mkdir($targetPath , 0755, true);
            }
                copy($source, $target);
            }

        // After successful export, notify the currently logged-in user
        try {
            $this->notifyLoggedInUserExportReady($udi, $exportUserEmail);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send export-ready notification: ' . $e->getMessage());
        }
    }

    private function notifyLoggedInUserExportReady($udi, $email): void
    {

        $addresses = [new Address($email)];

        $template = $this->twig->load('Email/data-repository-managers.dataset-export-ready.email.twig');
        $this->mailer->sendEmailMessage(
            $template,
            [
                'udi' => $udi
            ],
            $addresses,
        );

        $this->logger->info(sprintf(
            'Export-ready email sent to %s for UDI %s.',
            $email,
            $udi
        ));
    }

}
