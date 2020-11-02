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
     * Datastore utility instance for manipulating files on disk.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * The File Repository.
     *
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LoggerInterface $virusScanLogger Name hinted virus_scan logger.
     * @param VirusScanUtil   $scanner         A stream-based virus-scanning utility instance.
     * @param Datastore       $dataStore       Datastore utility instance.
     * @param FileRepository  $fileRepository  The file Repository.
    */
    public function __construct(
        LoggerInterface $virusScanLogger,
        VirusScanUtil $scanner,
        Datastore $datastore,
        FileRepository $fileRepository
    ) {
        $this->logger = $virusScanLogger;
        $this->scanner = $scanner;
        $this->datastore = $datastore;
        $this->fileRepository = $fileRepository;
    }

    /**
     * Invoke function to scan a file for viruses.
     *
     * @param VirusScan $virusScan The VirusScan message to be handled.
     */
    public function __invoke(ScanFileForVirus $scanFileForVirusMessage)
    {
        $fileId = $scanFileForVirusMessage->getId();
        $file = $this->fileRepository->find($fileId);
        $fileStream = $this->datastore->getFile($file->getPath());
        $result = $this->scanner->scanResourceStream($fileStream['fileStream']);
        if ($result['status'] == 'FOUND') {
            $loggingContext['fileId'] = $fileId;
            $loggingContext['reason'] = $result['reason'];
            $this->logger->alert('Virus found in file ID: {fileId}, VIRUS ID: {reason}.', $loggingContext);
        } elseif ($result['status'] !== 'failed') {
            $this->logger->info(sprintf('Virus scanned file ID: %s for UDI: %s. Status: %s.', $id, $udi, $result['status']));
        } else {
            if ($result['reason'] == 'oversize') {
                $this->logger->warning(sprintf('Filesize limit exceeded. Unable to scan file ID: %s for UDI: %s. Message: %s', $id, $udi, $result['reason']));
            } else {
                $this->logger->warning(sprintf('Unable to scan file ID: %s for UDI: %s. Message: %s', $id, $udi, $result['reason']));
            }
        }
    }
}
