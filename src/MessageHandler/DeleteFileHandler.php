<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\DeleteFile;
use App\Repository\FileRepository;

use App\Util\Datastore;
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
     * The file Repository.
     *
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * Datastore Utility instance.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LoggerInterface $deleteFileLogger Name hinted delete_file logger.
     * @param FileRepository  $fileRepository   The file Repository.
     * @param Datastore       $datastore        Datastore utility instance.
     */
    public function __construct(LoggerInterface $deleteFileLogger, FileRepository $fileRepository, Datastore $datastore)
    {
        $this->logger = $deleteFileLogger;
        $this->fileRepository = $fileRepository;
        $this->datastore = $datastore;
    }

    public function __invoke(DeleteFile $deleteFile)
    {
        $fileId = $deleteFile->getFilePath();
        $this->logger->info(sprintf('Processing File with ID: %d', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePath = $file->getFilePath();
            try {
                $this->datastore->deleteFile($filePath);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Unable to delete file. Message: %s', $e->getMessage()));
                return;
            }
        }
    }
}
