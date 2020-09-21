<?php

namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use App\Message\VirusScan;
use App\Entity\File;
use App\Repository\FileRepository;

class VirusScanHandler implements MessageHandlerInterface
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
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityManagerInterface   $entityManager           The entity handler.
     * @param FileRepository           $fileRepository          The file Repository.
     * @param LoggerInterface          $datasetFileHasherLogger Name hinted dataset_file_hasher logger.
     */
    public function __construct(EntityManagerInterface $entityManager, FileRepository $fileRepository, LoggerInterface $datasetFileHasherLogger)
    {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $datasetFileHasherLogger;
    }

    /**
     * Invoke function to scan a file for viruses.
     *
     * @param VirusScan $virusScan The VirusScan message to be handled.
     */
    public function __invoke(VirusScan $virusScan)
    {
        $fileId = $hashFile->getFileId();
        $this->logger->info(sprintf('Scanningg File with ID: %d', $fileId));
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePath = $file->getFilePath();
            try {
                $socket = (new \Socket\Raw\Factory())->createClient('unix:///run/clamd.scan/clamd.sock');
                $quahog = new \Xenolope\Quahog\Client($socket);
                $result = $quahog->scanFile($filePathh);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Unable to scan file. Message: %s', $e->getMessage()));
                // On catch, furter execution prevented, so attributes will not be updated.
                return;
            }
            //$file->setVirusScanDate($fileHash, '1974-12-02' );
            //$file->setVirusScanResult($fileHash);
            //$this->entityManager->flush();
        } else {
            $this->logger->alert(sprintf('File with ID: %d was not found to scan!', $fileId));
        }
    }
}
