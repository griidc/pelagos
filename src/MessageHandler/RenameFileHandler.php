<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\RenameFile;
use App\Repository\FileRepository;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class for RenameFileHandler.
 */
class RenameFileHandler implements MessageHandlerInterface
{
    /**
     * The Entity Manager.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * The File Repository.
     *
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Pelagos Datastore.
     *
     * @var Datastore
     */
    private $dataStore;

    /**
     * Constructor for rename file handler.
     *
     * @param EntityManagerInterface $entityManager    The entity handler.
     * @param FileRepository         $fileRepository   The file Repository.
     * @param LoggerInterface        $renameFileLogger Name hinted rename_file logger.
     * @param Datastore              $dataStore        Datastore utility instance.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository,
        LoggerInterface $renameFileLogger,
        Datastore $dataStore
    ) {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $renameFileLogger;
        $this->dataStore = $dataStore;
    }

    /**
     * Invoke function to rename files or mark files as deleted.
     *
     * @param RenameFile $renameFile
     */
    public function __invoke(RenameFile $renameFile)
    {
        $fileId = $renameFile->getFileId();
        $this->logger->info(sprintf('Rename File worker started with ID: %d', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePhysicalPath = $file->getPhysicalFilePath();
            if ($file->getStatus() === File::FILE_DELETED) {
                $this->logger->info(sprintf('Marking File as deleted for ID: %d', $fileId));
                $newFilePath = $filePhysicalPath . Datastore::MARK_FILE_AS_DELETED;
                $newFilePath = $this->dataStore->renameFile($filePhysicalPath, $newFilePath, true);
            } else {
                $this->logger->info(sprintf('File is renamed on disk for ID: %d', $fileId));
                $newFilePath = $this->dataStore->renameFile($filePhysicalPath, $file->getFileRootPath() . $file->getFilePathName());
            }
            $file->setPhysicalFilePath($newFilePath);
            $this->entityManager->flush();
            $this->logger->info('Rename File worker completed');
        } else {
            $this->logger->alert(sprintf('No file found for ID: %d', $fileId));
        }
    }
}
