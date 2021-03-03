<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;

use App\Event\EntityEventDispatcher;

use App\Message\DatasetSubmissionFiler;
use App\Message\ProcessFile;
use App\Message\ZipDatasetFiles;

use App\Repository\DatasetSubmissionRepository;

use App\Util\Datastore;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handler for dataset submission filer.
 */
class DatasetSubmissionFilerHandler implements MessageHandlerInterface
{
    /**
     * Dataset Submission repository instance.
     *
     * @var DatasetSubmissionRepository
     */
    private $datasetSubmissionRepository;

    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Instance of symfony messenger message bus.
     *
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * The entity event dispatcher.
     *
     * @var EntityEventDispatcher
     */
    protected $entityEventDispatcher;

    /**
     * The entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Datastore utility instance for manipulating files on disk.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * DatasetSubmissionFilerHandler constructor.
     *
     * @param DatasetSubmissionRepository $datasetSubmissionRepository Dataset Submission Repository.
     * @param LoggerInterface             $filerLogger                 Name hinted filer logger.
     * @param MessageBusInterface         $messageBus                  Symfony messenger bus interface instance.
     * @param EntityEventDispatcher       $entityEventDispatcher       The entity event dispatcher.
     * @param EntityManagerInterface      $entityManager               The entity manager.
     * @param Datastore                   $datastore                   Datastore utility instance.
     */
    public function __construct(
        DatasetSubmissionRepository $datasetSubmissionRepository,
        LoggerInterface $filerLogger,
        MessageBusInterface $messageBus,
        EntityEventDispatcher $entityEventDispatcher,
        EntityManagerInterface $entityManager,
        Datastore $datastore
    ) {
        $this->datasetSubmissionRepository = $datasetSubmissionRepository;
        $this->logger = $filerLogger;
        $this->messageBus = $messageBus;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
    }

    /**
     * Invoke function to process dataset submission filer.
     *
     * @param DatasetSubmissionFiler $datasetSubmissionFiler Dataset submission filer message to be handled.
     */
    public function __invoke(DatasetSubmissionFiler $datasetSubmissionFiler)
    {
        $datasetSubmissionId = $datasetSubmissionFiler->getDatasetSubmissionId();
        $datasetSubmission = $this->datasetSubmissionRepository->find($datasetSubmissionId);
        $dataset = $datasetSubmission->getDataset();
        $udi = $datasetSubmission->getDataset()->getUdi();
        $loggingContext = array(
            'dataset_id' => $dataset->getId(),
            'udi' => $udi,
            'dataset_submission_id' => $datasetSubmissionId
        );
        $fileset = $datasetSubmission->getFileset();
        if ($fileset instanceof Fileset) {
            // Log processing complete.
            $this->logger->info('Dataset submission process started', $loggingContext);

            if ($fileset->isDone()) {
                $fileIds = array();
                foreach ($fileset->getProcessedFiles() as $file) {
                    $fileIds[] = $file->getId();
                }
                // Dispatch message to zip files
                $zipFiles = new ZipDatasetFiles($fileIds, $datasetSubmissionId);
                $this->messageBus->dispatch($zipFiles);
            }

            foreach ($fileset->getNewFiles() as $file) {
                if ($file instanceof File) {
                    $fileId = $file->getId();
                    $processFile = new ProcessFile($fileId);
                    $this->messageBus->dispatch($processFile);
                } else {
                    $this->logger->alert('File object does not exist');
                }
            }

            $datasetSubmission->setDatasetFileTransferStatus(
                DatasetSubmission::TRANSFER_STATUS_COMPLETED
            );
            $dataset->updateAvailabilityStatus();
            $datasetSubmission->setDatasetFileSize($fileset->getFileSize());

            // Dispatch entity event.
            $this->entityEventDispatcher->dispatch($datasetSubmission, 'dataset_processed');
            $this->logger->info('Dataset submission process completed', $loggingContext);
        } else {
            if ($datasetSubmission->getRemotelyHostedUrl()) {
                $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
            }
        }
        $this->entityManager->flush();
    }
}
