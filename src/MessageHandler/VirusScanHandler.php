<?php

namespace App\MessageHandler;

use App\Message\ScanFileForVirus;
use App\Util\VirusScanUtil;
use Psr\Log\LoggerInterface;
use Socket\Raw\Factory as SocketFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Xenolope\Quahog\Client as QuahogClient;

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
     * @param LoggerInterface $virusScanLogger Name hinted virus_scan logger.
     * @param VirusScanUtil   $scanner         A stream-based virus-scanning utility instance.
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
        $udi = $scanFileForVirusMessage->getUdi();
        $id = $scanFileForVirusMessage->getId();
        $streamArray = $scanFileForVirusMessage->getStream();
        $result = $this->scanner->scanResourceStream($streamArray['fileStream']);
        if ($result['status'] == 'FOUND') {
            $this->logger->warning(sprintf('Virus found in file ID: %s for UDI: %s, VIRUS ID: %s.', $id, $udi, $result['reason']));
        } elseif ($result['status'] !== 'failed') {
            $this->logger->info(sprintf('Virus scanned file ID: %s for UDI: %s. Status: %s.', $id, $udi, $result['status']));
        } else {
            $this->logger->warning(sprintf('Unable to scan file ID: %s for UDI: %s. Message: %s', $id, $udi, $result['reason']));
        }
    }
}
