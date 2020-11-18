<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\DeleteFile;
use App\Repository\FileRepository;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteFileHandler implements MessageHandlerInterface
{
    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Datastore Utility instance.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * The File Repository.
     *
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * The Entity Manager.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * String appended to filename to mark as deleted.
     */
    const MARK_FILE_AS_DELETED = '_DELETED';

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LoggerInterface        $deleteFileLogger Name hinted delete_file logger.
     * @param Datastore              $datastore        Datastore utility instance.
     * @param FileRepository         $fileRepository   The file Repository.
     * @param EntityManagerInterface $entityManager    The entity manager interface.
     */
    public function __construct(
        LoggerInterface $deleteFileLogger,
        Datastore $datastore,
        FileRepository $fileRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->logger = $deleteFileLogger;
        $this->datastore = $datastore;
        $this->fileRepository = $fileRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(DeleteFile $deleteFile)
    {
        $fileId = $deleteFile->getFileId();
        $this->logger->info(sprintf('Marking file as deleted with ID: "%s"', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $oldFilePath = $file->getFilePath();
            $newFilePath = $oldFilePath . self::MARK_FILE_AS_DELETED;
            try {
                $this->datastore->renameFile($oldFilePath, $newFilePath);
                $file->setFilePath($newFilePath);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Unable to mark file as deleted. Message: "%s"', $e->getMessage()));
                return;
            }
        } else {
            $this->logger->alert(sprintf('File with ID: %d was not found!', $fileId));
        }
    }
}
