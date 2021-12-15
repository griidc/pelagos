<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\Fileset;
use App\Message\ZipDatasetFiles;
use App\Repository\FileRepository;
use App\Util\Datastore;
use App\Util\StreamInfo;
use App\Util\ZipFiles;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Handler for zipping files for download.
 */
class ZipDatasetFilesHandler implements MessageHandlerInterface
{
    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Zip files utility class.
     *
     * @var ZipFiles
     */
    private $zipFiles;

    /**
     * Download directory for the zip file.
     *
     * @var string
     */
    private $downloadDirectory;

    /**
     * The File Repository.
     *
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * Entity Manager interface instance.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Datastore utility instance.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * ZipDatasetFilesHandler constructor.
     *
     * @param LoggerInterface        $zipperLogger      A name hinted "<monologhandlername>Logger" Monolog logger interface.
     * @param ZipFiles               $zipFiles          Zip files utility instance.
     * @param string                 $downloadDirectory Temporary download directory path.
     * @param FileRepository         $fileRepository    File repository to query objects.
     * @param EntityManagerInterface $entityManager     Entity manager interface instance.
     * @param Datastore              $datastore         Datastore utility instance.
     */
    public function __construct(
        LoggerInterface $zipperLogger,
        ZipFiles $zipFiles,
        string $downloadDirectory,
        FileRepository $fileRepository,
        EntityManagerInterface $entityManager,
        Datastore $datastore
    ) {
        $this->logger = $zipperLogger;
        $this->zipFiles = $zipFiles;
        $this->downloadDirectory = $downloadDirectory;
        $this->fileRepository = $fileRepository;
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
    }

    /**
     * Invoke function to zip files for download.
     *
     * @param ZipDatasetFiles $zipDatasetFiles  Symfony message for zip dataset files.
     */
    public function __invoke(ZipDatasetFiles $zipDatasetFiles)
    {
        $datasetSubmissionId = $zipDatasetFiles->getDatasetSubmissionId();
        $datasetSubmission = $this->entityManager->getRepository(DatasetSubmission::class)->find($datasetSubmissionId);
        $udi = $datasetSubmission->getDataset()->getUdi();
        $loggingContext = array(
            'dataset_submission_id' => $datasetSubmissionId,
            'process' => getmypid(),
            'udi' => $udi,
        );
        $this->logger->info('Zip worker starting', $loggingContext);
        $fileIds = $zipDatasetFiles->getFileIds();
        $destinationPath = $this->downloadDirectory . DIRECTORY_SEPARATOR .  str_replace(':', '.', $udi) . '.zip';
        $fileset = $datasetSubmission->getFileset();
        if ($fileset->getStatus() === (Fileset::FILESET_BEING_ZIPPED || Fileset::FILESET_DONE)) {
            $this->logger->warning('Zipfile is already being zipped!: ' . $destinationPath, $loggingContext);
            return;
        }
        $filesInfo = $this->fileRepository->getFilePathNameAndPhysicalPath($fileIds);
        $this->logger->info('Zipfile opened: ' . $destinationPath, $loggingContext);
        try {
            $fileStream = fopen($destinationPath, 'w+');
            $lock = flock($fileStream, LOCK_EX);
            if (!$lock) {
                $this->logger->warning('Zipfile could not lock file: ' . $destinationPath, $loggingContext);
                return;
            }
            $outputStream = array('fileStream' => $fileStream);
            $this->zipFiles->start($outputStream, basename($destinationPath));
            foreach ($filesInfo as $fileItemInfo) {
                $this->logger->info("adding file to $destinationPath:" . $fileItemInfo['filePathName'], $loggingContext);
                $this->zipFiles->addFile($fileItemInfo['filePathName'], $this->datastore->getFile($fileItemInfo['physicalFilePath']));
            }
            $this->zipFiles->finish();
            $this->logger->info('Zipfile closed: ' . $destinationPath, $loggingContext);
            rewind($fileStream);
            $fileset->setStatus(Fileset::FILESET_DONE);
            $fileset->setZipFilePath($destinationPath);
            $fileset->setZipFileSize(StreamInfo::getFileSize($outputStream));
            $fileset->setZipFileSha256Hash(StreamInfo::calculateHash($outputStream, DatasetSubmission::SHA256));
            fclose($fileStream);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to zip file. Message: %s', $exception->getMessage()), $loggingContext);
            return;
        }
        $this->logger->info('Zip worker finished', $loggingContext);
    }
}
