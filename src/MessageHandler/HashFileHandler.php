<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Message\HashFile;
use App\Repository\FileRepository;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * The handler for file hashing messages
 */
class HashFileHandler implements MessageHandlerInterface
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
     * Invoke function to hash a file.
     *
     * @param HashFile $hashFile The HasFile message to be handled.
     */
    public function __invoke(HashFile $hashFile)
    {
        $fileId = $hashFile->getFileId();
        $this->logger->info(sprintf('Processing File with ID: %d', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePath = $file->getFilePath();
            try {
                $storedFile = $this->dataStore->getFile($filePath);
                $fileStream = $storedFile['fileStream'];

                $context = hash_init('sha256');
                hash_update_stream($context, $fileStream);
                $fileHash =  hash_final($context);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Unable to hash file. Message: %s', $e->getMessage()));
                return;
            }
            $file->setFileSha256Hash($fileHash);
            $this->entityManager->flush();
        } else {
            $this->logger->alert(sprintf('File with ID: %d was not found!', $fileId));
        }
    }
}
