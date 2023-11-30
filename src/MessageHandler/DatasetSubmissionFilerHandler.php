<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Event\EntityEventDispatcher;
use App\Message\DatasetSubmissionFiler;
use App\Message\ScanFileForVirus;
use App\Repository\DatasetSubmissionRepository;
use App\Util\Datastore;
use App\Util\StreamInfo;
use App\Util\ZipFiles;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
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
     * The entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The entity event dispatcher.
     *
     * @var EntityEventDispatcher
     */
    protected $entityEventDispatcher;

    /**
     * Pelagos Datastore.
     *
     * @var Datastore
     */
    private $datastore;


    /**
     * Download directory for the zip file.
     *
     * @var string
     */
    private $downloadDirectory;

    /**
     * DatasetSubmissionFilerHandler constructor.
     *
     * @param DatasetSubmissionRepository $datasetSubmissionRepository Dataset Submission Repository.
     * @param LoggerInterface             $filerLogger                 Name hinted filer logger.
     * @param MessageBusInterface         $messageBus                  Symfony messenger bus interface instance.
     * @param EntityManagerInterface      $entityManager               The entity manager.
     * @param string                      $downloadDirectory           Temporary download directory path.
     * @param ZipFiles                    $zipFiles                    Zip files utility instance.
     */
    public function __construct(
        DatasetSubmissionRepository $datasetSubmissionRepository,
        LoggerInterface $filerLogger,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager,
        EntityEventDispatcher $entityEventDispatcher,
        Datastore $datastore
    ) {
        $this->datasetSubmissionRepository = $datasetSubmissionRepository;
        $this->logger = $filerLogger;
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
        $this->entityEventDispatcher = $entityEventDispatcher;
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
        if (!$datasetSubmission instanceof DatasetSubmission) {
            $this->logger->error(sprintf('Can not find submission with ID: "%d"', $datasetSubmissionId));
            return;
        }
        $dataset = $datasetSubmission?->getDataset();
        $udi = $dataset?->getUdi();
        $loggingContext = array(
            'dataset_id' => $dataset?->getId(),
            'udi' => $udi,
            'dataset_submission_id' => $datasetSubmissionId,
            'process_id' => getmypid()
        );
        // Log processing start.
        $this->logger->info('Dataset submission process started', $loggingContext);

        $fileset = $datasetSubmission?->getFileset();
        if ($fileset instanceof Fileset) {
            foreach ($fileset->getQueuedFiles() as $file) {
                if ($file instanceof File) {
                    $this->processFile($file, $loggingContext);
                } else {
                    $this->logger->alert('File object does not exist');
                }
            }
        }

        $this->logger->info('Dataset submission all files done', $loggingContext);

        // Set Transfer Status to complete.
        $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        // Dispatch entity event.

        // Update dataset's availability status
        $dataset->updateAvailabilityStatus();

        $this->logger->info('Flushing data', $loggingContext);
        $this->entityManager->flush();
        $this->entityEventDispatcher->dispatch($datasetSubmission, 'dataset_processed');
        $this->logger->info('Dataset submission process completed', $loggingContext);
    }

    /**
     * Function to process file.
     *
     * Add a file to the datastore, and calculates hash,
     * and queue's for virus scan.
     *
     * @param File  $file           The File.
     * @param array $loggingContext Logging Context.
     *
     * @return void
     */
    private function processFile(File $file, array $loggingContext): void
    {
        $fileId = $file->getId();
        $fileset = $file->getFileset();
        $filePath = $file->getPhysicalFilePath();

        try {
            $resource = GuzzlePsr7Utils::tryFopen($filePath, 'r');
            $fileStream = GuzzlePsr7Utils::streamFor($resource);
        } catch (\Exception $e) {
            $lastErrorMessage = $e->getMessage();
            $this->logger->error(sprintf('Unreadable Queued File: "%s"', $lastErrorMessage), $loggingContext);
            $file->setDescription('Unreadable Queued File:' . $lastErrorMessage);
            $file->setStatus(File::FILE_ERROR);
            return;
        }

        $fileHash = StreamInfo::calculateHash($fileStream);
        $file->setFileSha256Hash($fileHash);

        try {
            $newFileDestination = $this->datastore->addFile(
                $fileStream,
                $fileset->getFileRootPath() . $file->getFilePathName()
            );
            $file->setPhysicalFilePath($newFileDestination);
        } catch (\League\Flysystem\Exception $fileExistException) {
            $this->logger->warning(sprintf('Rejecting: Unable to add file to datastore. Message: "%s"', $fileExistException->getMessage()), $loggingContext);
            $file->setDescription("Error writing to store:" . $fileExistException->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            return;
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setDescription("Error writing to store:" . $exception->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            return;
        }

        $file->setDescription('');
        $file->setStatus(File::FILE_DONE);

        $this->entityManager->flush();

        try {
            unlink($filePath);
            rmdir(dirname($filePath));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error delete file or folder. Message: "%s"', $exception->getMessage()), $loggingContext);
        }

         // File virus Scan
         $localLogContext = array_merge($loggingContext, array('fileId' => $fileId, 'filePathName' => $file->getFilePathName()));
         $this->messageBus->dispatch(new ScanFileForVirus($fileId, $loggingContext['udi']));
         $this->logger->info('Done processing file.', $localLogContext);
    }
}
