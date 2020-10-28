<?php

namespace App\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use App\Entity\DatasetSubmission;

use App\Message\ZipFile;

use App\Repository\FileRepository;

use App\Util\ZipFiles;

/**
 * Handler for zipping files for download.
 */
class ZipFileHandler implements MessageHandlerInterface
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
     * ZipFileHandler constructor.
     *
     * @param LoggerInterface        $logger            Default Monolog logger interface.
     * @param ZipFiles               $zipFiles          Zip files utility instance.
     * @param string                 $downloadDirectory Temporary download directory path.
     * @param FileRepository         $fileRepository    File repository to query objects.
     * @param EntityManagerInterface $entityManager     Entity manager interface instance.
     */
    public function __construct(
        LoggerInterface $logger,
        ZipFiles $zipFiles,
        string $downloadDirectory,
        FileRepository $fileRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->logger = $logger;
        $this->zipFiles = $zipFiles;
        $this->downloadDirectory = $downloadDirectory;
        $this->fileRepository = $fileRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Invoke function to zip files for download.
     *
     * @param ZipFile $zipFile
     */
    public function __invoke(ZipFile $zipFile)
    {
        $this->logger->info(sprintf('Zipping files started'));
        $fileIds = $zipFile->getFileIds();
        $datasetSubmissionId = $zipFile->getDatasetSubmissionId();
        $datasetSubmission = $this->entityManager->getRepository(DatasetSubmission::class)->find($datasetSubmissionId);
        $fileInfo = $this->fileRepository->getFileNameAndPath($fileIds);
        $destinationPath = $this->downloadDirectory . DIRECTORY_SEPARATOR . $datasetSubmission->getDataset()->getUdi() . '.zip';
        try {
            $this->zipFiles->createZipFile($fileInfo, $destinationPath);
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to zip file. Message: %s', $exception->getMessage()));
            return;
        }
        $datasetSubmission->getFileset()->setZipFilePath($destinationPath);
        $this->entityManager->flush();
        $this->logger->info(sprintf('Zipping files done'));
    }
}
