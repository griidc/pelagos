<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;

use App\Event\EntityEventDispatcher;
use App\Message\DatasetSubmissionFiler;
use App\Message\HashFile;
use App\Message\VirusScan;
use App\Repository\DatasetSubmissionRepository;

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
     * DatasetSubmissionFilerHandler constructor.
     *
     * @param DatasetSubmissionRepository $datasetSubmissionRepository Dataset Submission Repository.
     * @param LoggerInterface             $filerLogger                 Name hinted filer logger.
     * @param MessageBusInterface         $messageBus                  Symfony messenger bus interface instance.
     * @param EntityEventDispatcher       $entityEventDispatcher       The entity event dispatcher.
     * @param EntityManagerInterface      $entityManager               The entity manager.
     */
    public function __construct(
        DatasetSubmissionRepository $datasetSubmissionRepository,
        LoggerInterface $filerLogger,
        MessageBusInterface $messageBus,
        EntityEventDispatcher $entityEventDispatcher,
        EntityManagerInterface $entityManager
    ) {
        $this->datasetSubmissionRepository = $datasetSubmissionRepository;
        $this->logger = $filerLogger;
        $this->messageBus = $messageBus;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->entityManager = $entityManager;
    }

    /**
     * Invoke function to process dataset submission filer.
     *
     * @param DatasetSubmissionFiler $datasetSubmissionFiler Dataset submission filer message to be handled.
     */
    public function __invoke(DatasetSubmissionFiler $datasetSubmissionFiler)
    {
        $datasetSubmissionId = $datasetSubmissionFiler->getSubmissionId();
        $datasetSubmission = $this->datasetSubmissionRepository->find($datasetSubmissionId);
        $dataset = $datasetSubmission->getDataset();
        $loggingContext = array(
            'dataset_id' => $dataset->getId(),
            'udi' => $datasetSubmission->getDataset()->getUdi(),
            'dataset_submission_id' => $datasetSubmissionId
        );
        $fileset = $datasetSubmission->getFileset();

        if ($fileset instanceof Fileset) {
            // Log processing complete.
            $this->logger->info('Dataset submission process started', $loggingContext);
            foreach ($fileset->getFiles() as $file) {
                $this->processFile($file, $loggingContext);
            }

            $datasetSubmission->setDatasetFileTransferStatus(
                DatasetSubmission::TRANSFER_STATUS_COMPLETED
            );
            $dataset->updateAvailabilityStatus();
            $this->entityManager->persist($datasetSubmission);
            $this->entityManager->persist($dataset);
            $this->entityManager->flush();
            // Dispatch entity event.
            $this->entityEventDispatcher->dispatch($datasetSubmission, 'dataset_processed');
            $this->logger->info('Dataset submission process completed', $loggingContext);
        }
    }

    /**
     * Method to process a single file.
     *
     * @param File  $file           The file that is being processed.
     * @param array $loggingContext The logging context for the handler.
     *
     * @return void
     */
    private function processFile(File $file, array $loggingContext): void
    {
        // Log processing start.
        $fileId = $file->getId();
        $loggingContext['file_id'] = $fileId;
        $this->logger->info('Dataset file processing started', $loggingContext);

        // TODO implement method
        /*
        try {
            $fileInfo = $this->fileManager->moveFile($filePath);
            $file->setFilePath($fileInfo['filepath']);
        } catch (\Exception $exception) {
            $this->logger->error('Unable to process file', $loggingContext);
        }
        */

        // File Hashing
        $hashFile = new HashFile($fileId);
        $this->messageBus->dispatch($hashFile);

        // File virus Scan
        $scanFile = new VirusScan($fileId);
        $this->messageBus->dispatch($scanFile);

        // Log processing complete.
        $this->logger->info('Dataset file processing completed', $loggingContext);
    }
}
