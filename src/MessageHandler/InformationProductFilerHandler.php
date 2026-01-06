<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Entity\Person;
use App\Message\InformationProductFiler;
use App\Repository\InformationProductRepository;
use App\Util\Datastore;
use App\Util\StreamInfo;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler()]
final class InformationProductFilerHandler
{
    /**
     * Information Product Filer constructor.
     */
    public function __construct(
        private readonly InformationProductRepository $informationProductRepository,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
        protected EntityManagerInterface $entityManager,
        private readonly Datastore $datastore,
    ) {
    }

    public function __invoke(InformationProductFiler $informationProductFiler)
    {
        $informationProductId = $informationProductFiler->getInformationProductId();
        $informationProduct = $this->informationProductRepository->find($informationProductId);

        $loggingContext = ['information_product_id' => $informationProductId];

        $file = $informationProduct->getFile();
        if (!$file instanceof File) {
            $this->logger->error('No file for this IP, bye!', $loggingContext);

            return;
        }
        $file->getId();
        $filePath = $file->getPhysicalFilePath();
        $systemPerson = $this->entityManager->find(Person::class, 0);
        $file->setModifier($systemPerson);

        $this->logger->info('Information Product process started', $loggingContext);

        $destinationPath = 'information_products'
            . DIRECTORY_SEPARATOR . $informationProductId
            . DIRECTORY_SEPARATOR . $file->getFilePathName();

        try {
            $resource = GuzzlePsr7Utils::tryFopen($filePath, 'r');
            $fileStream = GuzzlePsr7Utils::streamFor($resource);
        } catch (\Exception $e) {
            $lastErrorMessage = $e->getMessage();
            $this->logger->error(sprintf('Unreadable File: "%s"', $lastErrorMessage), $loggingContext);
            $file->setDescription('Unreadable Queued File:' . $lastErrorMessage);
            $file->setStatus(File::FILE_ERROR);

            return;
        }

        $fileHash = StreamInfo::calculateHash($fileStream);
        $file->setFileSha256Hash($fileHash);

        try {
            $newFileDestination = $this->datastore->addFile($fileStream, $destinationPath);
            $file->setPhysicalFilePath($newFileDestination);
        } catch (FilesystemException $fileExistException) {
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

        if (is_resource($fileStream)) {
            @fclose($fileStream);
        }

        try {
            unlink($filePath);
            rmdir(dirname((string) $filePath));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Error delete file or folder. Message: "%s"', $exception->getMessage()), $loggingContext);
        }

        $file->setDescription('Information Product');
        $file->setStatus(File::FILE_DONE);

        $this->logger->info('Flushing data', $loggingContext);
        $this->entityManager->flush();
        $this->logger->info('Information Product filer process completed', $loggingContext);
    }
}
