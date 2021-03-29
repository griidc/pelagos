<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
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
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LoggerInterface        $deleteFileLogger Name hinted delete_file logger.
     * @param Datastore              $datastore        Datastore utility instance.
     */
    public function __construct(LoggerInterface $deleteFileLogger, Datastore $datastore)
    {
        $this->logger = $deleteFileLogger;
        $this->datastore = $datastore;
    }

    /**
     * Invoke function to mark file as deleted.
     *
     * @param DeleteFile $deleteFile The DeleteFile message to be handled.
     */
    public function __invoke(DeleteFile $deleteFile)
    {
        try {
            $this->datastore->deleteFile($deletedFile->getFilePath());
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to delete file. Message: "%s"', $e->getMessage()));
        }
    }
}
