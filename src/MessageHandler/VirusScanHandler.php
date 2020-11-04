<?php

namespace App\MessageHandler;

use App\Message\ScanFileForVirus;
use App\Repository\FileRepository;
use App\Util\Datastore;
use App\Util\VirusScanUtil;
use Psr\Log\LoggerInterface;
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
     * @param Datastore       $datastore       Datastore utility instance.
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
     * @param ScanFileForVirus $scanFileForVirusMessage The VirusScan message to be handled.
     */
    public function __invoke(ScanFileForVirus $scanFileForVirusMessage)
    {
        $fileId = $scanFileForVirusMessage->getFileId();
        $udi = $scanFileForVirusMessage->getUdi();
        $file = $this->fileRepository->find($fileId);
        try {
            $fileStream = $this->datastore->getFile($file->getFilePath());
            $result = $this->scanner->scanResourceStream($fileStream);
            if ($result['status'] === QuahogClient::RESULT_FOUND) {
                $loggingContext['fileId'] = $fileId;
                $loggingContext['reason'] = $result['reason'];
                $this->logger->alert('Virus found in file ID: {fileId}, VIRUS ID: {reason}.', $loggingContext);
            } elseif ($result['status'] !== VirusScanUtil::RESULT_STATUS_FAILED) {
                $this->logger->info(sprintf('Virus scanned file ID: %s for UDI: %s. Status: %s.', $fileId, $udi, $result['status']));
            } else {
                if ($result['reason'] === VirusScanUtil::RESULT_REASON_OVERSIZE) {
                    $this->logger->warning(sprintf('Filesize limit exceeded. Unable to scan file ID: %s for UDI: %s. Message: %s', $fileId, $udi, $result['reason']));
                } else {
                    $this->logger->warning(sprintf('Unable to scan file ID: %s for UDI: %s. Message: %s', $fileId, $udi, $result['reason']));
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("File stream not found for fileId: {$fileId}");
        }
    }
}
