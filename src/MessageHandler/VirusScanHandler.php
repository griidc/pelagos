<?php

namespace App\MessageHandler;

use App\Util\VirusScanUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Socket\Raw\Factory as SocketFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Xenolope\Quahog\Client as QuahogClient;
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
     * The virus-scanning utility holder.
     *
     * @var VirusScanUtil $scanner
     */
    private $scanner;


    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityManagerInterface $entityManager   The entity handler.
     * @param FileRepository         $fileRepository  The file Repository.
     * @param LoggerInterface        $virusScanLogger Name hinted virus_scan logger.
     * @param VirusScanUtil          $scanner         A stream-based virus-scanning utility instance.
    */
    public function __construct(EntityManagerInterface $entityManager, FileRepository $fileRepository, LoggerInterface $virusScanLogger, VirusScanUtil $scanner)
    {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->logger = $virusScanLogger;
        $this->scanner = $scanner;
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
            $filePath = $file->getFilepath();
            if (file_exists($filePath)) {
                $fileSize = filesize($filePath);
                if ($fileSize <= 1047527424) {
                    $fileHandle = fopen($filePath, 'r');
                    $result = $this->scanner->ScanResourceStream($fileHandle);
                    fclose($fileHandle);
                    if ($result['status'] == 'FOUND') {
                        $this->logger->warning(sprintf('Virus found in file id:%s filename: %s. VIRUS ID: %s.', $fileId, $filePath, $result['reason']));
                    } elseif ($result['status'] !== 'failed') {
                        $this->logger->info(sprintf('Virus scanned file id: %s filename: %s. Status: %s.', $fileId, $filePath, $result['status']));
                    } else {
                        $this->logger->warning(sprintf('Unable to scan file. Message: %s', $result['reason']));
                    }
                } else {
                        $this->logger->warning(sprintf('Unable to scan files over 1GB. Not scanning %s.', $filePath));
                }
            } else {
                $this->logger->warning(sprintf('Unable to scan missing file: %s.', $filePath));
            }
        }
    }
}
