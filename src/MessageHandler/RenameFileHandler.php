<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\RenameFile;
use App\Repository\FileRepository;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class for RenameFileHandler.
 */
#[AsMessageHandler()]
class RenameFileHandler
{
    /**
     * Constructor for rename file handler.
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileRepository $fileRepository,
        private readonly LoggerInterface $logger,
        private readonly Datastore $dataStore,
    ) {
    }

    /**
     * Invoke function to rename files or mark files as deleted.
     */
    public function __invoke(RenameFile $renameFile)
    {
        $fileId = $renameFile->getFileId();
        $this->logger->info(sprintf('Rename File worker started with ID: %d', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePhysicalPath = $file->getPhysicalFilePath();
            if (File::FILE_DELETED === $file->getStatus()) {
                $this->logger->info(sprintf('Marking File as deleted for ID: %d', $fileId));
                $newFilePath = $filePhysicalPath . Datastore::MARK_FILE_AS_DELETED;
                $newFilePath = $this->dataStore->renameFile($filePhysicalPath, $newFilePath, true);
            } else {
                $this->logger->info(sprintf('File is renamed on disk for ID: %d', $fileId));
                $newFilePath = $this->dataStore->renameFile($filePhysicalPath, $file->getFileset()->getFileRootPath() . $file->getFilePathName());
            }
            $file->setPhysicalFilePath($newFilePath);
            $this->entityManager->flush();
            $this->logger->info('Rename File worker completed');
        } else {
            $this->logger->alert(sprintf('No file found for ID: %d', $fileId));
        }
    }
}
