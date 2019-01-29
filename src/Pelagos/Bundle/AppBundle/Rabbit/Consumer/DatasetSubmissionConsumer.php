<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Doctrine\ORM\EntityManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

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
     * The dataset file hasher AQMP producer.
     *
     * @var Producer
     */
    protected $datasetFileHasherProducer;

    /**
     * Constructor.
     *
     * @param EntityManager         $entityManager             The entity manager.
     * @param DataStore             $dataStore                 The data store service.
     * @param Logger                $logger                    A Monolog logger.
     * @param EntityEventDispatcher $entityEventDispatcher     The entity event dispatcher.
     * @param MdappLogger           $mdappLogger               A MDAPP logger.
     * @param Producer              $datasetFileHasherProducer The dataset file hasher AQMP producer.
     */
    public function __construct(
        EntityManager $entityManager,
        DataStore $dataStore,
        Logger $logger,
        EntityEventDispatcher $entityEventDispatcher,
        MdappLogger $mdappLogger,
        Producer $datasetFileHasherProducer
    ) {
        $this->entityManager = $entityManager;
        $this->dataStore = $dataStore;
        $this->logger = $logger;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->mdappLogger = $mdappLogger;
        $this->datasetFileHasherProducer = $datasetFileHasherProducer;
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
        $datasetSubmissionId = $message->body;

        // Clear Doctrine's cache to force loading from persistence.
        $this->entityManager->clear();
        $datasetSubmission = $this->entityManager
                        ->getRepository(DatasetSubmission::class)
                        ->find($datasetSubmissionId);
        $dataset = $datasetSubmission->getDataset();
        $loggingContext = array('dataset_id' => $datasetSubmission->getDataset()->getId());
        if (!$dataset instanceof Dataset) {
            $this->logger->warning('No dataset found', $loggingContext);
            return true;
        }
        if (null !== $dataset->getUdi()) {
            $loggingContext['udi'] = $dataset->getUdi();
        }

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
            $dataset->updateAvailabilityStatus();
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

        // Log processing start.
        $this->logger->info('Dataset file processing starting', $loggingContext);
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
            $datasetFileName = $this->dataStore->addFile($datasetFileUri, $datasetId, 'dataset');
            $datasetSubmission->setDatasetFileName($datasetFileName);
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
        // Publish an AMQP message to trigger dataset file hashing.
        $this->datasetFileHasherProducer->publish($datasetSubmission->getId());
    }
}
