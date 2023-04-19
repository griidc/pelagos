<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Person;
use App\Message\InformationProductFiler;
use App\Message\ScanFileForVirus;
use App\Repository\InformationProductRepository;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class InformationProductFilerHandler implements MessageHandlerInterface
{
    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Information Product Filer constructor.
     *
     * @param LoggerInterface $ipFileLogger      name hinted filer logger
     * @param string          $downloadDirectory temporary download directory path
     */
    public function __construct(
        private InformationProductRepository $informationProductRepository,
        LoggerInterface $ipFilerLogger,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
        private Datastore $datastore
    ) {
        $this->logger = $ipFilerLogger;
    }

    public function __invoke(InformationProductFiler $informationProductFiler)
    {
        $informationProductId = $informationProductFiler->getInformationProductId();
        $informationProduct = $this->informationProductRepository->find($informationProductId);

        $loggingContext = [
            'information_product_id' => $informationProductId,
        ];

        $this->logger->info('Information Product process started', $loggingContext);

        $file = $informationProduct->getFile();
        if (!$file instanceof File) {
            $this->logger->error('No file for this IP, bye!', $loggingContext);

            return;
        }
        $fileId = $file->getId();
        $filePath = $file->getPhysicalFilePath();

        $systemPerson = $this->entityManager->find(Person::class, 0);
        $file->setModifier($systemPerson);

        $destinationPath = 'information_products'
            . DIRECTORY_SEPARATOR . $informationProductId
            . DIRECTORY_SEPARATOR . $file->getFilePathName();

        try {
            $fileStream = Utils::streamFor(fopen($filePath, 'r'));
        } catch (\RuntimeException $e) {
            $lastErrorMessage = $e->getMessage();
            $this->logger->error(sprintf('Unreadable File: "%s"', $lastErrorMessage, $loggingContext));
            $file->setDescription('Unreadable Queued File:' . $lastErrorMessage);
            $file->setStatus(File::FILE_ERROR);

            return;
        }

        $file->setFileSha256Hash(Utils::hash($fileStream, DatasetSubmission::SHA256));

        try {
            $newFileDestination = $this->datastore->addFile(
                fileStream: $fileStream,
                filePathName: $destinationPath
            );
            $file->setPhysicalFilePath($newFileDestination);
        } catch (\League\Flysystem\Exception $fileExistException) {
            $this->logger->warning(sprintf('Rejecting: Unable to add file to datastore. Message: "%s"', $fileExistException->getMessage()), $loggingContext);
            $file->setDescription('Error writing to store:' . $fileExistException->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            throw new \Exception($fileExistException->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setDescription('Error writing to store:' . $exception->getMessage());
            $file->setStatus(File::FILE_ERROR);

            return;
        }

        $fileStream->close();

        try {
            unlink($filePath);
            rmdir(dirname($filePath));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error delete file or folder. Message: "%s"', $exception->getMessage()), $loggingContext);
        }

        // File virus Scan
        $this->logger->info("Enqueuing virus scan for file: {$file->getFilePathName()}.", $loggingContext);
        $this->messageBus->dispatch(new ScanFileForVirus($fileId, "INFORMATION.PRODUCT:$informationProductId"));

        $file->setDescription('Information Product');
        $file->setStatus(File::FILE_DONE);

        $this->logger->info('Flushing data', $loggingContext);
        $this->entityManager->flush();
        $this->logger->info('Information Product filer process completed', $loggingContext);
    }
}
