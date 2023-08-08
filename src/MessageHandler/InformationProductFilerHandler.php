<?php

namespace App\MessageHandler;

use App\Entity\File;
use App\Entity\Person;
use App\Message\InformationProductFiler;
use App\Message\ScanFileForVirus;
use App\Repository\InformationProductRepository;
use App\Util\Datastore;
use App\Util\StreamInfo;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class InformationProductFilerHandler implements MessageHandlerInterface
{
    /**
     * The information product repository.
     *
     * @var InformationProductRepository
     */
    private $informationProductRepository;

    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Instance of symfony messenger message bus.
     *
     * @var MessageBusInterface
     */
    private $messageBus;

    /**
     * The entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Information Product Filer constructor.
     *
     * @param InformationProductRepository $datasetSubmissionRepository Dataset Submission Repository.
     * @param LoggerInterface              $ipFileLogger                 Name hinted filer logger.
     * @param EntityManagerInterface       $entityManager               The entity manager.
     * @param string                       $downloadDirectory           Temporary download directory path.
     */
    public function __construct(
        InformationProductRepository $informationProductRepository,
        LoggerInterface $ipFilerLogger,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager,
        Datastore $datastore
    ) {
        $this->informationProductRepository = $informationProductRepository;
        $this->logger = $ipFilerLogger;
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
    }
    public function __invoke(InformationProductFiler $informationProductFiler)
    {
        $informationProductId = $informationProductFiler->getInformationProductId();
        $informationProduct = $this->informationProductRepository->find($informationProductId);

        $loggingContext = array(
            'information_product_id' => $informationProductId,
        );

        $file = $informationProduct->getFile();
        if (!$file instanceof File) {
            $this->logger->error("No file for this IP, bye!", $loggingContext);
            return;
        }
        $fileId = $file->getId();
        $filePath = $file->getPhysicalFilePath();
        @$fileStream = fopen($filePath, 'r');

        $systemPerson = $this->entityManager->find(Person::class, 0);
        $file->setModifier($systemPerson);

        $this->logger->info('Information Product process started', $loggingContext);

        $destinationPath = 'information_products'
            . DIRECTORY_SEPARATOR .  $informationProductId
            . DIRECTORY_SEPARATOR . $file->getFilePathName();

        if ($fileStream === false) {
            $lastErrorMessage = error_get_last()['message'];
            $this->logger->error(sprintf('Unreadable File: "%s"', $lastErrorMessage, $loggingContext));
            $file->setDescription('Unreadable Queued File:' . $lastErrorMessage);
            $file->setStatus(File::FILE_ERROR);
            return;
        } else {
            $fileHash = StreamInfo::calculateHash(array('fileStream' => $fileStream));
            $file->setFileSha256Hash($fileHash);
        }

        try {
            $newFileDestination = $this->datastore->addFile(
                ['fileStream' => $fileStream],
                $destinationPath
            );
            $file->setPhysicalFilePath($newFileDestination);
        } catch (\League\Flysystem\Exception $fileExistException) {
            $this->logger->warning(sprintf('Rejecting: Unable to add file to datastore. Message: "%s"', $fileExistException->getMessage()), $loggingContext);
            $file->setDescription("Error writing to store:" . $fileExistException->getMessage());
            $file->setStatus(File::FILE_ERROR);
            $this->entityManager->flush();
            throw new \Exception($fileExistException->getMessage());
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to add file to datastore. Message: "%s"', $exception->getMessage()), $loggingContext);
            $file->setDescription("Error writing to store:" . $exception->getMessage());
            $file->setStatus(File::FILE_ERROR);
            return;
        }

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
