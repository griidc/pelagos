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
     * @param EntityManagerInterface $entityManager    A Doctrine EntityManager.
     */
    public function __construct(LoggerInterface $deleteFileLogger, Datastore $datastore, EntityManagerInterface $entityManager)
    {
        $this->logger = $deleteFileLogger;
        $this->datastore = $datastore;
        $this->entityManager = $entityManager;
    }

    /**
     * Invoke function to mark file as deleted.
     *
     * @param DeleteFile $deleteFile The DeleteFile message to be handled.
     */
    public function __invoke(DeleteFile $deleteFile)
    {
        $datasetSubmissionId = $deleteFile->getDatasetSubmissionId();

        $this->logger->info(sprintf('Processing File with ID: "%s"', $datasetSubmissionId));

        $datasetSubmission = $this->entityManager->getRepository(DatasetSubmission::class)->find($datasetSubmissionId);

        if (!$datasetSubmission instanceof DatasetSubmission) {
            $this->logger->warning('Submission not found');
            return;
        }

        $fileset = $datasetSubmission->getFileset();

        if (!$fileset instanceof Fileset) {
            $this->logger->warning('fileset not found');
            return;
        }

        foreach ($fileset->getDeletedFiles() as $deletedFile) {
            try {
                $this->datastore->deleteFile($deletedFile->getPhysicalFilePath());
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Unable to delete file. Message: "%s"', $e->getMessage()));
                return;
            }
            $fileset->removeFile($deletedFile);

        }

        $this->entityManager->flush($datasetSubmission);
    }
}
