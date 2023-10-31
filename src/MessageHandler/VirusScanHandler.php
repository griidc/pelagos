<?php

namespace App\MessageHandler;

use App\Message\ScanFileForVirus;
use App\Repository\FileRepository;
use App\Util\Datastore;
use App\Util\VirusScanUtil;
use Psr\Log\LoggerInterface;
use Socket\Raw\Factory as SocketFactory;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Xenolope\Quahog\Client as QuahogClient;
use Xenolope\Quahog\Result;

class VirusScanHandler implements MessageHandlerInterface
{
    /**
     * The ClamAV socket file.
     *
     * @var string
     */
    private $clamdSock;

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
     * Constructor for this Controller, to set up default 1ces.
     *
     * @param LoggerInterface $virusScanLogger Name hinted virus_scan logger.
     * @param VirusScanUtil   $scanner         A stream-based virus-scanning utility instance.
     * @param Datastore       $datastore       Datastore utility instance.
     * @param FileRepository  $fileRepository  The file Repository.
     * @param string          $clamdSock       The location from .env of clamd daemon.
    */
    public function __construct(
        LoggerInterface $virusScanLogger,
        VirusScanUtil $scanner,
        Datastore $datastore,
        FileRepository $fileRepository,
        string $clamdSock

    ) {
        $this->logger = $virusScanLogger;
        $this->scanner = $scanner;
        $this->datastore = $datastore;
        $this->fileRepository = $fileRepository;
        $this->clamdSock = $clamdSock;
    }

    /**
     * Invoke function to scan a file for viruses.
     *
     * @param ScanFileForVirus $scanFileForVirusMessage The VirusScan message to be handled.
     */
    public function __invoke(ScanFileForVirus $scanFileForVirusMessage)
    {
        $fileId = $scanFileForVirusMessage->getFileId();
        $udi = $scanFileForVirusMessage->getUdi();
        $file = $this->fileRepository->find($fileId);
        $loggingContext['fileId'] = $fileId;
        $loggingContext['filepath'] = $file->getPhysicalFilePath();

        $loggingContext['process_id'] = getmypid();
        $fileStream = $this->datastore->getFile($file->getPhysicalFilePath());
        $fileSize = $fileStream->getSize();
        if ($fileSize > 104857600) {
            $this->logger->warning(sprintf('Filesize limit exceeded. Unable to scan file ID: %s for UDI: %s. Message: %s', $fileId, $udi, 'oversize'), $loggingContext);
            return;
        } else {
            try {
                $socket = (new SocketFactory())->createClient($this->clamdSock);
                $quahog = new QuahogClient($socket);
                $resource = $fileStream->detach();
                $result = $quahog->scanResourceStream($resource, 1024000);

                if ($result instanceof Result) {
                    $reason = $result->getReason();
                    if ($result->isFound()) {
                        $this->logger->alert("Virus found in file ID: $fileId, VIRUS ID: $reason", $loggingContext);
                    } else {
                        $this->logger->info(sprintf('Virus scanned file ID: %s for UDI: %s. Status: %s.', $fileId, $udi, $reason ?? ''), $loggingContext);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error("File stream not found for fileId: {$fileId}", $loggingContext);
                return;
            }
        }
    }
}
