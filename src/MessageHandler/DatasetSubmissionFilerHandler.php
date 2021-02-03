<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;

use App\Event\EntityEventDispatcher;

use App\Message\DatasetSubmissionFiler;
use App\Message\HashFile;
use App\Message\ScanFileForVirus;

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
            foreach ($fileset->getNewFiles() as $file) {
                if ($file instanceof File) {
                    $fileId = $file->getId();
                    $processFile = new processFile($fileId);
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

    /**
     * Method to process a single file.
     *
     * @param File   $file           The file that is being processed.
     * @param string $udi            The UDI of the dataset.
     * @param array  $loggingContext The logging context for the related dataset submission.
     *
     * @return void
     */
    private function processFile(File $file, string $udi, array $loggingContext): void
    {
        // Log processing start.
        $fileId = $file->getId();
        $loggingContext['file_id'] = $fileId;
        $filepath = $file->getPhysicalFilePath();

        $this->logger->info('Dataset file processing started', $loggingContext);
        $file->setStatus(File::FILE_IN_PROGRESS);

        try {
            $newFileDestination = $this->datastore->addFile(
                ['fileStream' => fopen($filepath, 'r')],
                str_replace(':', '.', $udi) . DIRECTORY_SEPARATOR . $file->getFilePathName()
            );
            $file->setPhysicalFilePath($newFileDestination);
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setStatus(File::FILE_ERROR);
        }

        // File Hashing
        $hashFile = new HashFile($fileId);
        $this->messageBus->dispatch($hashFile);

        // File virus Scan
        $this->messageBus->dispatch(new ScanFileForVirus($fileId, $loggingContext['udi']));
        $this->logger->info("Enqueuing virus scan for file: {$file->getFilePathName()}.", $loggingContext);

        // Log processing complete.
        $this->logger->info('Dataset file processing completed', $loggingContext);
    }
}
