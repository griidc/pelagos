<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Event\EntityEventDispatcher;
use App\Message\ProcessFile;
use App\Message\ScanFileForVirus;
use App\Message\ZipDatasetFiles;
use App\Util\Datastore;
use App\Util\StreamInfo;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * The handler for file hashing messages
 */
class ProcessFileHandler implements MessageHandlerInterface
{
    /**
     * The Entity Manager.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Pelagos Datastore.
     *
     * @var Datastore
     */
    private $datastore;

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
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityManagerInterface $entityManager           The entity handler.
     * @param LoggerInterface        $fileProcessingLogger    Name hinted dataset_file_hasher logger.
     * @param Datastore              $datastore               Datastore utility instance.
     * @param MessageBusInterface    $messageBus              Symfony messenger bus interface instance.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $fileProcessingLogger,
        Datastore $datastore,
        MessageBusInterface $messageBus,
        EntityEventDispatcher $entityEventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $fileProcessingLogger;
        $this->datastore = $datastore;
        $this->messageBus = $messageBus;
        $this->entityEventDispatcher = $entityEventDispatcher;
    }

    /**
     * Invoke function to process a file.
     *
     * @param ProcessFile $processFile The Process File message to be handled.
     */
    public function __invoke(ProcessFile $processFile)
    {
        // Create message array to store messages.
        $messages = array();
        $fileId = $processFile->getFileId();
        $file = $this->entityManager->getRepository(File::class)->find($fileId);

        if (!$file instanceof File) {
            $this->logger->alert(sprintf('File with ID: %d was not found!', $fileId));
            return;
        }

        $fileset = $file->getFileset();
        $datasetSubmission = $fileset->getDatasetSubmission();
        $dataset = $datasetSubmission->getDataset();
        $udi = $dataset->getUdi();
        $loggingContext = array(
            'fileId' => $fileId,
            'dataset_id' => $dataset->getId(),
            'udi' => $udi,
            'dataset_submission_id' => $datasetSubmission->getId(),
        );

        $filePath = $file->getPhysicalFilePath();
        @$fileStream = fopen($filePath, 'r');

        if ($fileStream === false) {
            $lastErrorMessage = error_get_last()['message'];
            $this->logger->error(sprintf('Unreadable Queued File: "%s"', $lastErrorMessage, $loggingContext));
            $file->setDescription('Unreadable Queued File:' . $lastErrorMessage);
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            return;
        } else {
            $fileHash = StreamInfo::calculateHash(array('fileStream' => $fileStream));
            $file->setFileSha256Hash($fileHash);
        }

        try {
            $newFileDestination = $this->datastore->addFile(
                ['fileStream' => $fileStream],
                $fileset->getFileRootPath() . $file->getFilePathName()
            );
            $file->setPhysicalFilePath($newFileDestination);
        } catch (\League\Flysystem\Exception $fileExistException) {
            $this->logger->warning(sprintf('Rejecting: Unable to add file to datastore. Message: "%s"', $fileExistException->getMessage()), $loggingContext);
            $file->setDescription("Error writing to store:" . $fileExistException->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            throw new \Exception($fileExistException->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setDescription("Error writing to store:" . $exception->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            return;
        }

        try {
            unlink($filePath);
            rmdir(dirname($filePath));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error delete file or folder. Message: "%s"', $exception->getMessage()), $loggingContext);
        }

        // File virus Scan
        $this->logger->info("Enqueuing virus scan for file: {$file->getFilePathName()}.", $loggingContext);
        $this->messageBus->dispatch(new ScanFileForVirus($fileId, $loggingContext['udi']));

        $file->setDescription('');
        $file->setStatus(File::FILE_DONE);

        $this->logger->info('Flushing data', $loggingContext);
        $this->entityManager->flush();

        if ($fileset->isDone()) {
            $datasetSubmissionId = $datasetSubmission->getId();
            $fileIds = array();
            foreach ($fileset->getProcessedFiles() as $file) {
                $fileIds[] = $file->getId();
            }
            // Dispatch message to zip files
            $this->logger->info('All files are done, zipping', $loggingContext);

            $datasetSubmission->setDatasetFileTransferStatus(
                DatasetSubmission::TRANSFER_STATUS_COMPLETED
            );

            // Dispatch entity event.
            $this->entityEventDispatcher->dispatch($datasetSubmission, 'dataset_processed');
            // Update dataset's availability status
            $dataset->updateAvailabilityStatus();
            $this->entityManager->flush();
            $this->messageBus->dispatch(new ZipDatasetFiles($fileIds, $datasetSubmissionId));
        } else {
            $this->logger->info('Processed file for Dataset', $loggingContext);
        }
    }
}
