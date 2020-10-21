<?php

namespace App\MessageHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use App\Message\ZipFile;

use App\Repository\FilesetRepository;

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
     * The Fileset Repository.
     *
     * @var FilesetRepository
     */
    private $filesetRepository;

    /**
     * ZipFileHandler constructor.
     *
     * @param LoggerInterface   $logger            Default Monolog logger interface.
     * @param ZipFiles          $zipFiles          Zipfiles utility instance.
     * @param string            $downloadDirectory Temporary download directory path.
     * @param FilesetRepository $filesetRepository Fileset repository to query objects.
     */
    public function __construct(LoggerInterface $logger, ZipFiles $zipFiles, string $downloadDirectory, FilesetRepository $filesetRepository)
    {
        $this->logger = $logger;
        $this->zipFiles = $zipFiles;
        $this->downloadDirectory = $downloadDirectory;
        $this->filesetRepository = $filesetRepository;
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
       $files = new ArrayCollection($this->filesetRepository->findBy($fileIds));
       $destinationPath = $this->downloadDirectory . DIRECTORY_SEPARATOR . Uuid::uuid4()->toString() . '.zip';
       try {
           $this->zipFiles->createZipFile($files, $destinationPath);
       } catch (\Exception $exception) {
           $this->logger->error(sprintf('Unable to zip file. Message: %s', $exception->getMessage()));
           return;
       }
       $this->logger->info(sprintf('Zipping files done'));
    }
}
