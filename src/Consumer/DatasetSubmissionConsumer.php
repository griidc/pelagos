<?php

namespace App\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use PhpAmqpLib\Message\AMQPMessage;

use Symfony\Bridge\Monolog\Logger;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;

use App\Event\EntityEventDispatcher;

use App\Exception\HtmlFoundException;

use App\Util\DataStore;
use App\Util\RabbitPublisher;

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
     * @var EntityManagerInterface
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
     * The entity event dispatcher.
     *
     * @var EntityEventDispatcher
     */
    protected $entityEventDispatcher;

    /**
     * Custom rabbitmq publisher.
     *
     * @var RabbitPublisher
     */
    protected $publisher;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager         The entity manager.
     * @param DataStore              $dataStore             The data store service.
     * @param Logger                 $logger                A Monolog logger.
     * @param EntityEventDispatcher  $entityEventDispatcher The entity event dispatcher.
     * @param RabbitPublisher        $publisher             The dataset file hasher AQMP producer.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DataStore $dataStore,
        Logger $logger,
        EntityEventDispatcher $entityEventDispatcher,
        RabbitPublisher $publisher
    ) {
        $this->entityManager = $entityManager;
        $this->dataStore = $dataStore;
        $this->logger = $logger;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->publisher = $publisher;
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
        // phpcs:disable
        $routingKey = $message->delivery_info['routing_key'];
        // phpcs:enable
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
        $this->publisher->publish($datasetSubmission->getId(), RabbitPublisher::FILE_HASHER_PRODUCER);
    }
}
