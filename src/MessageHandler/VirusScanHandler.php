<?php

namespace App\MessageHandler;

use App\Util\VirusScanUtil;
use Psr\Log\LoggerInterface;
use Socket\Raw\Factory as SocketFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Xenolope\Quahog\Client as QuahogClient;
use App\Message\ScanFileForVirus;

class VirusScanHandler implements MessageHandlerInterface
{
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
     * @param LoggerInterface        $virusScanLogger Name hinted virus_scan logger.
     * @param VirusScanUtil          $scanner         A stream-based virus-scanning utility instance.
    */
    public function __construct(LoggerInterface $virusScanLogger, VirusScanUtil $scanner)
    {
        $this->logger = $virusScanLogger;
        $this->scanner = $scanner;
    }

    /**
     * Invoke function to scan a file for viruses.
     *
     * @param VirusScan $virusScan The VirusScan message to be handled.
     */
    public function __invoke(ScanFileForVirus $scanFileForVirusMessage)
    {
        $filename = $scanFileForVirusMessage->getFilePathAndName();
        if (file_exists($filename)) {
            $fileSize = filesize($filename);
            if ($fileSize <= 1047527424) {
                $fileHandle = fopen($filename, 'r');
                $result = $this->scanner->scanResourceStream($fileHandle);
                fclose($fileHandle);
                if ($result['status'] == 'FOUND') {
                    $this->logger->warning(sprintf('Virus found in filename: %s. VIRUS ID: %s.', $filename, $result['reason']));
                } elseif ($result['status'] !== 'failed') {
                    $this->logger->info(sprintf('Virus scanned filename: %s. Status: %s.', $filename, $result['status']));
                } else {
                    $this->logger->warning(sprintf('Unable to scan filename: %s. Message: %s', $filename, $result['reason']));
                }
            } else {
                    $this->logger->warning(sprintf('Unable to scan files over 1GB. Not scanning filename: %s.', $filename));
            }
        } else {
            $this->logger->warning(sprintf('Unable to scan missing filename: %s.', $filename));
        }
    }
}
