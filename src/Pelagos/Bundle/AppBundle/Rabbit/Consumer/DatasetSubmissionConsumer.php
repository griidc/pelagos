<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Doctrine\ORM\EntityManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use PhpAmqpLib\Message\AMQPMessage;

use Symfony\Bridge\Monolog\Logger;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

use Pelagos\Event\EntityEventDispatcher;

use Pelagos\Exception\HtmlFoundException;

use Pelagos\Util\DataStore;
use Pelagos\Util\MdappLogger;

/**
 * A consumer of dataset submission messages.
 *
 * @see ConsumerInterface
 */
class DatasetSubmissionConsumer implements ConsumerInterface
{
    /**
     * The entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The data store service.
     *
     * @var DataStore
     */
    protected $dataStore;

    /**
     * A Monolog logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * A MDAPP logger.
     *
     * @var MdappLogger
     */
    protected $mdappLogger;

    /**
     * The entity event dispatcher.
     *
     * @var EntityEventDispatcher
     */
    protected $entityEventDispatcher;

    /**
     * Constructor.
     *
     * @param EntityManager         $entityManager         The entity manager.
     * @param DataStore             $dataStore             The data store service.
     * @param Logger                $logger                A Monolog logger.
     * @param EntityEventDispatcher $entityEventDispatcher The entity event dispatcher.
     * @param MdappLogger           $mdappLogger           A MDAPP logger.
     */
    public function __construct(
        EntityManager $entityManager,
        DataStore $dataStore,
        Logger $logger,
        EntityEventDispatcher $entityEventDispatcher,
        MdappLogger $mdappLogger
    ) {
        $this->entityManager = $entityManager;
        $this->dataStore = $dataStore;
        $this->logger = $logger;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->mdappLogger = $mdappLogger;
    }

    /**
     * Process a filer message.
     *
     * @param AMQPMessage $message A filer message.
     *
     * @return boolean True if success, false otherwise.
     */
    public function execute(AMQPMessage $message)
    {
        $datasetId = $message->body;
        $loggingContext = array('dataset_id' => $datasetId);
        // Clear Doctrine's cache to force loading from persistence.
        $this->entityManager->clear();
        $dataset = $this->entityManager
                        ->getRepository(Dataset::class)
                        ->find($datasetId);
        if (!$dataset instanceof Dataset) {
            $this->logger->warning('No dataset found', $loggingContext);
            return true;
        }
        if (null !== $dataset->getUdi()) {
            $loggingContext['udi'] = $dataset->getUdi();
        }
        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!$datasetSubmission instanceof DatasetSubmission) {
            $this->logger->warning('No submission found for dataset', $loggingContext);
            return true;
        }
        $loggingContext['dataset_submission_id'] = $datasetSubmission->getId();
        // @codingStandardsIgnoreStart
        $routingKey = $message->delivery_info['routing_key'];
        // @codingStandardsIgnoreEnd
        if (preg_match('/^dataset\./', $routingKey)) {
            $this->processDataset($datasetSubmission, $loggingContext);
        } elseif (preg_match('/^metadata\./', $routingKey)) {
            $this->processMetadata($datasetSubmission, $loggingContext);
        } else {
            $this->logger->warning("Unknown routing key: $routingKey", $loggingContext);
            return true;
        }
        $this->entityManager->persist($datasetSubmission);
        $this->entityManager->persist($dataset);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Process the dataset for a dataset submission.
     *
     * @param DatasetSubmission $datasetSubmission The dataset submission to process.
     * @param array             $loggingContext    The logging context to use when logging.
     *
     * @return void
     */
    protected function processDataset(DatasetSubmission $datasetSubmission, array $loggingContext)
    {
        $datasetFileTransferStatus = $datasetSubmission->getDatasetFileTransferStatus();
        if ($datasetFileTransferStatus !== DatasetSubmission::TRANSFER_STATUS_NONE) {
            $this->logger->warning(
                "Unexpected dataset file transfer status: $datasetFileTransferStatus",
                $loggingContext
            );
            return;
        }
        try {
            $datasetFileUri = $datasetSubmission->getDatasetFileUri();
            $datasetId = $datasetSubmission->getDataset()->getUdi();
            $this->dataStore->addFile($datasetFileUri, $datasetId, 'dataset');
            $datasetSubmission->setDatasetFileName(basename($datasetFileUri));
            $datasetFileInfo = $this->dataStore->getFileInfo($datasetId, 'dataset');
            $datasetSubmission->setDatasetFileSize($datasetFileInfo->getSize());
            $datasetSubmission->setDatasetFileTransferStatus(
                DatasetSubmission::TRANSFER_STATUS_COMPLETED
            );
        } catch (HtmlFoundException $exception) {
            $datasetSubmission->setDatasetFileTransferStatus(
                DatasetSubmission::TRANSFER_STATUS_NEEDS_REVIEW
            );
            $this->entityEventDispatcher->dispatch($datasetSubmission, 'html_found');
            $this->logger->error('Error processing dataset: ' . $exception->getMessage(), $loggingContext);
            return;
        } catch (\Exception $exception) {
            $datasetSubmission->setDatasetFileTransferStatus(
                DatasetSubmission::TRANSFER_STATUS_NEEDS_REVIEW
            );
            $this->entityEventDispatcher->dispatch($datasetSubmission, 'dataset_unprocessable');
            $this->logger->error('Error processing dataset: ' . $exception->getMessage(), $loggingContext);
            return;
        }
        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($datasetSubmission, 'dataset_processed');
        // Log processing complete.
        $this->logger->info('Dataset file processing complete', $loggingContext);
    }

    /**
     * Process the metadata for a dataset submission.
     *
     * @param DatasetSubmission $datasetSubmission The dataset submission to process.
     * @param array             $loggingContext    The logging context to use when logging.
     *
     * @return void
     */
    protected function processMetadata(DatasetSubmission $datasetSubmission, array $loggingContext)
    {
        $metadataFileTransferStatus = $datasetSubmission->getMetadataFileTransferStatus();
        if ($metadataFileTransferStatus !== DatasetSubmission::TRANSFER_STATUS_NONE) {
            $this->logger->warning(
                "Unexpected metadata file transfer status: $metadataFileTransferStatus",
                $loggingContext
            );
            return;
        }
        try {
            $metadataFileUri = $datasetSubmission->getMetadataFileUri();
            $datasetId = $datasetSubmission->getDataset()->getUdi();
            $this->dataStore->addFile($metadataFileUri, $datasetId, 'metadata');
            $mdSpiFileInfo = $this->dataStore->getFileInfo($datasetId, 'metadata');
            $mdSha256Hash = hash('sha256', file_get_contents($mdSpiFileInfo->getRealPath()));
            $datasetSubmission->setMetadataFileSha256Hash($mdSha256Hash);
            $datasetSubmission->setMetadataFileName(basename($metadataFileUri));
            $datasetSubmission->setMetadataFileTransferStatus(
                DatasetSubmission::TRANSFER_STATUS_COMPLETED
            );
            $datasetSubmission->setMetadataStatus(
                DatasetSubmission::METADATA_STATUS_SUBMITTED
            );
        } catch (\Exception $exception) {
            $this->logger->error('Error processing metadata: ' . $exception->getMessage(), $loggingContext);
            return;
        }

        $xferString = $datasetSubmission->getMetadataFileTransferType();
        if (null === $xferString) {
            $this->logger->error(
                "Error processing metadata: unexpected null metadatafiletransfertype for dataset: $datasetId.",
                $loggingContext
            );
            return;
        } elseif (array_key_exists($xferString, DatasetSubmission::TRANSFER_TYPES)) {
            $xferType = DatasetSubmission::TRANSFER_TYPES[$xferString];
            $mdappMsg = " $username has registered new metadata via $xferType for $datasetId.";
        } else {
            $this->logger->error(
                'Error processing metadata: unexpected metadatafiletransfertype of: '
                    . "$xferString for dataset: $datesetId.",
                $loggingContext
            );
            return;
        }

        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($datasetSubmission, 'metadata_processed');

        if (isset($mdappMsg) and (null !== $mdappMsg)) {
            $this->mdappLogger->writeLog($mdappMsg);
        }

        // Log processing complete.
        $this->logger->info('Metadata file processing complete', $loggingContext);
        $username = $datasetSubmission->getModifier()->getAccount()->getUsername();
    }
}
