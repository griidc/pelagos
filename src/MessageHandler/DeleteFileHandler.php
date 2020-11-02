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
     * Datastore Utility instance.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LoggerInterface $deleteFileLogger Name hinted delete_file logger.
     * @param Datastore       $datastore        Datastore utility instance.
     */
    public function __construct(LoggerInterface $deleteFileLogger, Datastore $datastore)
    {
        $this->logger = $deleteFileLogger;
        $this->datastore = $datastore;
    }

    public function __invoke(DeleteFile $deleteFile)
    {
        $filePath = $deleteFile->getFilePath();
        $this->logger->info(sprintf('Processing File with ID: "%s"', $filePath));
        try {
            $this->datastore->deleteFile($filePath);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to delete file. Message: "%s"', $e->getMessage()));
            return;
        }
    }
}
