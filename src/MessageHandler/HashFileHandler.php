<?php

namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use App\Message\HashFile;
use App\Entity\File;
use App\Repository\FileRepository;

/**
 * The handler for file hashing messages
 */
class HashFileHandler implements MessageHandlerInterface
{
    /**
     * The entity handler.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * The file Repository.
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
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityHandler   $entityHandler           The entity handler.
     * @param FileRepository  $fileRepository          The file Repository.
     * @param LoggerInterface $datasetFileHasherLogger Name hinted dataset_file_hasher logger.
     */
    public function __construct(EntityManagerInterface $entityManager, FileRepository $fileRepository, LoggerInterface $datasetFileHasherLogger)
    {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $datasetFileHasherLogger;
    }

    /**
     * Invoke function to hash a file.
     */
    public function __invoke(HashFile $hashFile)
    {
        $fileId = $hashFile->getFileId();
        $this->logger->info(sprintf('Processing File with ID: %d', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePath = $file->getFilePath();
            try {
                $fileHash = hash_file('sha256', 'bla');
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
