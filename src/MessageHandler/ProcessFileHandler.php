<?php

namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use App\Message\ProcessFile;
use App\Entity\File;
use App\Repository\FileRepository;
use App\Util\Datastore;

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
    private $dataStore;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityManagerInterface $entityManager           The entity handler.
     * @param FileRepository         $fileRepository          The file Repository.
     * @param LoggerInterface        $datasetFileHasherLogger Name hinted dataset_file_hasher logger.
     * @param Datastore              $dataStore               Datastore utility instance.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository,
        LoggerInterface $datasetFileHasherLogger,
        Datastore $dataStore
    ) {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $datasetFileHasherLogger;
        $this->dataStore = $dataStore;
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
        if ($file instanceof File) {
            $filePath = $file->getPhysicalFilePath();
            $fileStream = fopen($filepath, 'r');
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
            
            $fileset = $file->getFileset();
            $datasetSubmission = $fileset->getDatasetSubmission();
            
            if ($fileset->isDone()) {
                $datasetSubmissionId = $datasetSubmission->getId();
                $fileIds = array();
                foreach ($fileset->getProcessedFiles() as $file) {
                    $fileIds[] = $file->getId();
                }
                // Dispatch message to zip files
                $zipFiles = new ZipDatasetFiles($fileIds, $datasetSubmissionId);
                $this->messageBus->dispatch($zipFiles);
                $this->logger->info('Dataset file processing completed', $loggingContext);
            } else {
                $this->logger->info('Processed file for Dataset', $loggingContext);
            }
            
            $this->entityManager->flush();
        } else {
            $this->logger->alert(sprintf('File with ID: %d was not found!', $fileId));
        }
    }
 
    // /**
     // * Function to hash a file.
     // *
     // * @param int $fileId The HasFile message to be handled.
     // */
    // public function hashFile(int $fileId)
    // {
        // $this->logger->info(sprintf('Processing File with ID: %d', $fileId));
        // $file = $this->fileRepository->find($fileId);
        // if ($file instanceof File) {
            // $filePath = $file->getPhysicalFilePath();
            // $fileStream = fopen($filepath, 'r');
            // try {
                // $context = hash_init('sha256');
                // hash_update_stream($context, $fileStream);
                // $fileHash =  hash_final($context);
            // } catch (\Exception $e) {
                // $this->logger->error(sprintf('Unable to hash file. Message: %s', $e->getMessage()));
                // return;
            // }
            // $file->setFileSha256Hash($fileHash);
            // $this->entityManager->flush();
        // } else {
            // $this->logger->alert(sprintf('File with ID: %d was not found!', $fileId));
        // }
    // }
    
    // /**
     // * Method to process a single file.
     // *
     // * @param File   $file           The file that is being processed.
     // * @param string $udi            The UDI of the dataset.
     // * @param array  $loggingContext The logging context for the related dataset submission.
     // *
     // * @return void
     // */
    // private function processFile(File $file, string $udi, array $loggingContext): void
    // {
        // // Log processing start.
        // $fileId = $file->getId();
        // $loggingContext['file_id'] = $fileId;
        // $filepath = $file->getPhysicalFilePath();

        // $this->logger->info('Dataset file processing started', $loggingContext);
        // $file->setStatus(File::FILE_IN_PROGRESS);

        // try {
            // $newFileDestination = $this->datastore->addFile(
                // ['fileStream' => fopen($filepath, 'r')],
                // str_replace(':', '.', $udi) . DIRECTORY_SEPARATOR . $file->getFilePathName()
            // );
            // $file->setPhysicalFilePath($newFileDestination);
        // } catch (\Exception $exception) {
            // $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            // $file->setStatus(File::FILE_ERROR);
        // }

        // // File Hashing
        // $hashFile = new HashFile($fileId);
        // $this->messageBus->dispatch($hashFile);

        // // File virus Scan
        // $this->messageBus->dispatch(new ScanFileForVirus($fileId, $loggingContext['udi']));
        // $this->logger->info("Enqueuing virus scan for file: {$file->getFilePathName()}.", $loggingContext);

        // // Log processing complete.
        // $this->logger->info('Dataset file processing completed', $loggingContext);
    // }
}
