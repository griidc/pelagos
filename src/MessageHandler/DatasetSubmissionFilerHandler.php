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
use App\Util\ZipFiles;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handler for dataset submission filer.
 */
class DatasetSubmissionFilerHandler implements MessageHandlerInterface
{
    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Download directory for the zip file.
     *
     * @var string
     */
    private $downloadDirectory;

    /**
     * DatasetSubmissionFilerHandler constructor.
     *
     * @param LoggerInterface $filerLogger       name hinted filer logger
     * @param string          $downloadDirectory temporary download directory path
     */
    public function __construct(
        private DatasetSubmissionRepository $datasetSubmissionRepository,
        LoggerInterface $filerLogger,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
        private EntityEventDispatcher $entityEventDispatcher,
        private Datastore $datastore,
        string $downloadDirectory,
    ) {
        $this->logger = $filerLogger;
        $this->downloadDirectory = $downloadDirectory;
    }

    /**
     * Invoke function to process dataset submission filer.
     */
    public function __invoke(DatasetSubmissionFiler $datasetSubmissionFiler)
    {
        $datasetSubmissionId = $datasetSubmissionFiler->getDatasetSubmissionId();
        $datasetSubmission = $this->datasetSubmissionRepository->find($datasetSubmissionId);
        $dataset = $datasetSubmission->getDataset();
        $udi = $datasetSubmission->getDataset()->getUdi();
        $loggingContext = [
            'dataset_id' => $dataset->getId(),
            'udi' => $udi,
            'dataset_submission_id' => $datasetSubmissionId,
            'process_id' => getmypid(),
        ];
        // Log processing start.
        $this->logger->info('Dataset submission process started', $loggingContext);
        $destinationPath = $this->downloadDirectory . DIRECTORY_SEPARATOR . str_replace(':', '.', $datasetSubmission->getDataset()->getUdi()) . '.zip';

        $fileset = $datasetSubmission->getFileset();
        if ($fileset instanceof Fileset) {
            foreach ($fileset->getQueuedFiles() as $file) {
                if ($file instanceof File) {
                    $this->processFile($file, $loggingContext);
                } else {
                    $this->logger->alert('File object does not exist');
                }
            }
            $filesInfo = [];
            foreach ($fileset->getProcessedFiles() as $file) {
                $filesInfo[$file->getId()]['filePathName'] = $file->getFilePathName();
                $filesInfo[$file->getId()]['physicalFilePath'] = $file->getPhysicalFilePath();
            }
            $this->logger->info('Zipfile opened: ' . $destinationPath, array_merge($loggingContext, ['PHP_memory_usage' => memory_get_usage()]));
            $outputStream = Utils::streamFor(fopen($destinationPath, 'w+'));
            $zipFiles = new zipFiles($outputStream, basename($destinationPath));
            foreach ($filesInfo as $fileItemInfo) {
                $this->logger->info("adding file to $destinationPath:" . $fileItemInfo['filePathName'], $loggingContext);
                $zipFiles->addFile($fileItemInfo['filePathName'], $this->datastore->getFile($fileItemInfo['physicalFilePath']));
            }
            $this->logger->info('Zipfile to be closed: ' . $destinationPath, array_merge($loggingContext, ['PHP_memory_usage' => memory_get_usage()]));
            $zipFiles->finish();
            $this->logger->info('Zipfile closed: ' . $destinationPath, array_merge($loggingContext, ['PHP_memory_usage' => memory_get_usage()]));
            $fileset->setZipFilePath($destinationPath);
            $fileset->setZipFileSize($outputStream->getSize());
            $fileset->setZipFileSha256Hash(Utils::hash(stream: $outputStream, algo: DatasetSubmission::SHA256));
            $this->logger->info('All files are done, zipping', $loggingContext);
            $this->logger->info('Dataset submission all files done', $loggingContext);
        }

        // Set Transfer Status to complete.
        $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($datasetSubmission, 'dataset_processed');
        // Update dataset's availability status
        $dataset->updateAvailabilityStatus();

        $this->logger->info('Flushing data', $loggingContext);
        $this->entityManager->flush();
        $this->logger->info('Dataset submission process completed', $loggingContext);
    }

    /**
     * Function to process file.
     *
     * Add a file to the datastore, and calculates hash,
     * and queue's for virus scan.
     *
     * @param File  $file           the File
     * @param array $loggingContext logging Context
     */
    private function processFile(File $file, array $loggingContext): void
    {
        $fileId = $file->getId();
        $fileset = $file->getFileset();
        $filePath = $file->getPhysicalFilePath();

        try {
            $fileStream = utils::streamFor(fopen($filePath, 'r'));
        } catch (\RuntimeException $e) {
            $lastErrorMessage = $e->getMessage();
            $this->logger->error(sprintf('Unreadable Queued File: "%s"', $lastErrorMessage, $loggingContext));
            $file->setDescription('Unreadable Queued File:' . $lastErrorMessage);

            return;
        }

        $fileHash = Utils::hash($fileStream, DatasetSubmission::SHA256);
        $file->setFileSha256Hash($fileHash);

        try {
            $newFileDestination = $this->datastore->addFile(
                $fileStream,
                $fileset->getFileRootPath() . $file->getFilePathName()
            );
            $file->setPhysicalFilePath($newFileDestination);
        } catch (\League\Flysystem\Exception $fileExistException) {
            $this->logger->warning(sprintf('Rejecting: Unable to add file to datastore. Message: "%s"', $fileExistException->getMessage()), $loggingContext);
            $file->setDescription('Error writing to store:' . $fileExistException->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setDescription('Error writing to store:' . $exception->getMessage());
            $file->setStatus(File::FILE_ERROR);

            return;
        }

        try {
            unlink($filePath);
            rmdir(dirname($filePath));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error delete file or folder. Message: "%s"', $exception->getMessage()), $loggingContext);
        }

        // File virus Scan
        $localLogContext = array_merge($loggingContext, ['fileId' => $fileId, 'filePathName' => $file->getFilePathName()]);
        $this->messageBus->dispatch(new ScanFileForVirus($fileId, $loggingContext['udi']));
        $this->logger->info('Dispatched ScanFileForVirus message for async processing.', $localLogContext);

        $file->setDescription('');
        $file->setStatus(File::FILE_DONE);
    }
}
