<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\DeleteFile;
use App\Repository\FileRepository;

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
     * Constructor for this Controller, to set up default services.
     *
     * @param LoggerInterface $deleteFileLogger Name hinted delete_file logger.
     * @param FileRepository  $fileRepository   The file Repository.
     *
     */
    public function __construct(LoggerInterface $deleteFileLogger, FileRepository $fileRepository)
    {
        $this->logger = $deleteFileLogger;
        $this->fileRepository = $fileRepository;
    }

    public function __invoke(DeleteFile $deleteFile)
    {
        $fileId = $deleteFile->getFileId();
        $this->logger->info(sprintf('Processing File with ID: %d', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePath = $file->getFilePath();
            try {
                // TODO implement method
                //$this->fileManager->deleteFile($filePath);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Unable to hash file. Message: %s', $e->getMessage()));
                return;
            }
        }
    }
}
