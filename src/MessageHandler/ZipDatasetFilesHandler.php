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
use GuzzleHttp\Psr7\Utils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler for zipping files for download.
 */
#[AsMessageHandler()]
class ZipDatasetFilesHandler
{
    /**
     * ZipDatasetFilesHandler constructor.
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ZipFiles $zipFiles,
        private readonly string $downloadDirectory,
        private readonly FileRepository $fileRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Datastore $datastore,
    ) {
    }

    /**
     * Invoke function to zip files for download.
     *
     * @param ZipDatasetFiles $zipDatasetFiles symfony message for zip dataset files
     */
    public function __invoke(ZipDatasetFiles $zipDatasetFiles)
    {
        $loggingContext['process_id'] = getmypid();
        $this->logger->info('ZipDatasetFilesHandler worker starting.', $loggingContext);
        $datasetSubmissionId = $zipDatasetFiles->getDatasetSubmissionId();
        $datasetSubmission = $this->entityManager->getRepository(DatasetSubmission::class)->find($datasetSubmissionId);
        $udi = $datasetSubmission->getDataset()->getUdi();
        $destinationPath = $this->downloadDirectory . DIRECTORY_SEPARATOR . str_replace(':', '.', $udi) . '.zip';
        $loggingContext['UDI'] = $udi;
        $loggingContext['destination_path'] = $destinationPath;
        $fileset = $datasetSubmission->getFileset();
        if (!$fileset instanceof Fileset) {
            $this->logger->info('Not a fileset', $loggingContext);

            return;
        }
        $fileIds = [];
        foreach ($fileset->getProcessedFiles() as $file) {
            $fileIds[] = $file->getId();
        }
        $filesInfo = $this->fileRepository->getFilePathNameAndPhysicalPath($fileIds);
        try {
            $this->logger->info('Zipfile opened.', $loggingContext);
            $resource = Utils::tryFopen($destinationPath, 'w+');
            $outputStream = Utils::streamFor($resource);
            $this->zipFiles->start($outputStream, basename($destinationPath));
            foreach ($filesInfo as $fileItemInfo) {
                $this->logger->info('adding file: ' . $fileItemInfo['filePathName'] . '.', $loggingContext);
                $this->zipFiles->addFile($fileItemInfo['filePathName'], $this->datastore->getFile($fileItemInfo['physicalFilePath']));
            }
            $this->zipFiles->finish();
            $this->logger->info('Zipfile closed. ', $loggingContext);
            $fileset = $datasetSubmission->getFileset();
            $fileset->setZipFilePath($destinationPath);
            $fileset->setZipFileSize(StreamInfo::getFileSize($outputStream));
            $fileset->setZipFileSha256Hash(StreamInfo::calculateHash($outputStream, DatasetSubmission::SHA256));
            fclose($resource);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to zip file. Message: %s', $exception->getMessage()), $loggingContext);

            return;
        }
        $this->logger->info('ZipDatasetFilesHandler worker finished.', $loggingContext);
    }
}
