<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Event\EntityEventDispatcher;
use App\Message\DatasetSubmissionFiler;
use App\Repository\DatasetSubmissionRepository;
use App\Util\Datastore;
use App\Util\StreamInfo;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handler for dataset submission filer.
 */
#[AsMessageHandler()]
class DatasetSubmissionFilerHandler
{
    /**
     * DatasetSubmissionFilerHandler constructor.
     */
    public function __construct(
        private readonly DatasetSubmissionRepository $datasetSubmissionRepository,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityEventDispatcher $entityEventDispatcher,
        private readonly Datastore $datastore,
    ) {
    }

    /**
     * Invoke function to process dataset submission filer.
     *
     * @param DatasetSubmissionFiler $datasetSubmissionFiler dataset submission filer message to be handled
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
        $loggingContext = ['dataset_id' => $dataset?->getId(), 'udi' => $udi, 'dataset_submission_id' => $datasetSubmissionId, 'process_id' => getmypid()];
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
     * Add a file to the datastore, and calculates hash.
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
        } catch (FilesystemException $fileExistException) {
            $this->logger->warning(sprintf('Rejecting: Unable to add file to datastore. Message: "%s"', $fileExistException->getMessage()), $loggingContext);
            $file->setDescription('Error writing to store:' . $fileExistException->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();

            return;
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setDescription('Error writing to store:' . $exception->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();

            return;
        }

        $file->setDescription('');
        $file->setStatus(File::FILE_DONE);

        $this->entityManager->flush();

        try {
            unlink($filePath);
            rmdir(dirname((string) $filePath));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error delete file or folder. Message: "%s"', $exception->getMessage()), $loggingContext);
        }

        $localLogContext = array_merge($loggingContext, ['fileId' => $fileId, 'filePathName' => $file->getFilePathName()]);
        $this->logger->info('Done processing file.', $localLogContext);
    }
}
