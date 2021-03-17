<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\ProcessFile;
use App\Message\ScanFileForVirus;
use App\Message\ZipDatasetFiles;
use App\Repository\FileRepository;
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
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $fileProcessingLogger;
        $this->datastore = $datastore;
        $this->messageBus = $messageBus;
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
        $fileStream = fopen($filePath, 'r');
        try {
            $fileHash = StreamInfo::calculateHash(array('fileStream' => $fileStream));
            $file->setFileSha256Hash($fileHash);
        } catch (\Exception $e) {
            $this->logger->warning(sprintf('Unable to hash file. Message: %s', $e->getMessage()));
        }

        try {
            $newFileDestination = $this->datastore->addFile(
                ['fileStream' => $fileStream],
                $file->getFileRootPath() . $file->getFilePathName()
            );
            $file->setPhysicalFilePath($newFileDestination);
            unlink($filePath);
            rmdir(dirname($filePath));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            return;
        }

        // File virus Scan
        $this->logger->info("Enqueuing virus scan for file: {$file->getFilePathName()}.", $loggingContext);
        $this->messageBus->dispatch(new ScanFileForVirus($fileId, $loggingContext['udi']));

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
            $zipFiles = new ZipDatasetFiles($fileIds, $datasetSubmissionId);
            $this->messageBus->dispatch(new ZipDatasetFiles($fileIds, $datasetSubmissionId));

            // Update dataset's availability status
            $dataset->updateAvailabilityStatus();
            $this->entityManager->flush();
        } else {
            $this->logger->info('Processed file for Dataset', $loggingContext);
        }
    }
}
