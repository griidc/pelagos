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
     * @param LoggerInterface          $virusScanLogger         Name hinted virus_scan logger.
     */
    public function __construct(EntityManagerInterface $entityManager, FileRepository $fileRepository, LoggerInterface $virusScanLogger)
    {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $virusScanLogger;
    }

    /**
     * Invoke function to scan a file for viruses.
     *
     * @param VirusScan $virusScan The VirusScan message to be handled.
     */
    public function __invoke(VirusScan $virusScan)
    {
        $fileId = $virusScan->getFileId();
        $file = $this->fileRepository->find($fileId);
        if ($file instanceof File) {
            $filePath = $file->getFilePath();
            try {
                $socket = (new \Socket\Raw\Factory())->createClient('unix:///run/clamd.scan/clamd.sock');
                $quahog = new \Xenolope\Quahog\Client($socket);
                $result = $quahog->scanFile($filePath);
                $this->logger->info(sprintf('Virus scanned file id:%s filename: %s. Status: %s. Reason: %s.', $fileId, $result['filename'], $result['status'], $result['reason']));
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Unable to scan file. Message: %s', $e->getMessage()));
                // On catch, furter execution prevented, so attributes will not be updated.
                return;
            }
            // In the future, we'll likely set an attribute of the scan results, or an email for identified viruses, etc.
            //$file->setVirusLastScanDate();
            //$file->setVirusLastScanResult($result['status']);
            //$this->entityManager->flush();
        } else {
            $this->logger->alert(sprintf('File with ID: %d was not found to scan!', $fileId));
        }
    }
}
