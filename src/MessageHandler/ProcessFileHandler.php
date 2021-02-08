<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\ProcessFile;
use App\Message\ScanFileForVirus;
use App\Message\ZipDatasetFiles;
use App\Repository\FileRepository;
use App\Util\Datastore;
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
     * The File Repository.
     *
     * @var FileRepository
     */
    private $fileRepository;

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
     * @param FileRepository         $fileRepository          The file Repository.
     * @param LoggerInterface        $datasetFileHasherLogger Name hinted dataset_file_hasher logger.
     * @param Datastore              $datastore               Datastore utility instance.
     * @param MessageBusInterface    $messageBus              Symfony messenger bus interface instance.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository,
        LoggerInterface $datasetFileHasherLogger,
        Datastore $datastore,
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $datasetFileHasherLogger;
        $this->datastore = $datastore;
        $this->messageBus = $messageBus;
    }

    /**
     * Destructor for the handler to always flush.
     */
    public function __destruct() {
        $this->entityManager->flush();
    }

    /**
     * Invoke function to process a file.
     *
     * @param ProcessFile $processFile The HasFile message to be handled.
     */
    public function __invoke(ProcessFile $processFile)
    {
        $fileId = $processFile->getFileId();
        $file = $this->fileRepository->find($fileId);

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

        $file->setStatus(File::FILE_IN_PROGRESS);
        $filePath = $file->getPhysicalFilePath();
        $fileStream = fopen($filePath, 'r');
        try {
            $context = hash_init('sha256');
            hash_update_stream($context, $fileStream);
            $fileHash =  hash_final($context);
            $file->setFileSha256Hash($fileHash);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to hash file. Message: %s', $e->getMessage()));
            return;
        }

        try {
            $newFileDestination = $this->datastore->addFile(
                ['fileStream' => $fileStream],
                str_replace(':', '.', $udi) . DIRECTORY_SEPARATOR . $file->getFilePathName()
            );
            $file->setPhysicalFilePath($newFileDestination);
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setStatus(File::FILE_ERROR);
        }

        // File virus Scan
        $this->messageBus->dispatch(new ScanFileForVirus($fileId, $loggingContext['udi']));
        $this->logger->info("Enqueuing virus scan for file: {$file->getFilePathName()}.", $loggingContext);

        $file->setStatus(File::FILE_DONE);
        

        if ($fileset->isDone()) {
            $datasetSubmissionId = $datasetSubmission->getId();
            $fileIds = array();
            foreach ($fileset->getProcessedFiles() as $file) {
                $fileIds[] = $file->getId();
            }
            dump($fileIds);
            // Dispatch message to zip files
            $zipFiles = new ZipDatasetFiles($fileIds, $datasetSubmissionId);
            $this->entityManager->flush();
            $this->messageBus->dispatch($zipFiles);
            $this->logger->info('Dataset file processing completed', $loggingContext);
        } else {
            $this->logger->info('Processed file for Dataset', $loggingContext);
        }
    }
}
