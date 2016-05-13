<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Doctrine\ORM\EntityManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use PhpAmqpLib\Message\AMQPMessage;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

use Pelagos\Util\DataStore;

/**
 * A consumer of filer messages.
 *
 * @see ConsumerInterface
 */
class FilerConsumer implements ConsumerInterface
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
     * Constructor.
     *
     * @param EntityManager $entityManager The entity manager.
     * @param DataStore     $dataStore     The data store service.
     */
    public function __construct(EntityManager $entityManager, DataStore $dataStore)
    {
        $this->entityManager = $entityManager;
        $this->dataStore = $dataStore;
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
        $messageData = json_decode($message->body);
        // Clear Doctrine's cache to force loading from persistence.
        $this->entityManager->clear();
        $dataset = $this->entityManager
                        ->getRepository(Dataset::class)
                        ->find($messageData->datasetId);
        if (!$dataset instanceof Dataset) {
            // Log bad id.
            echo "No dataset found with id: $messageData->datasetId\n";
            return true;
        }
        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!$datasetSubmission instanceof DatasetSubmission) {
            // Log no submission.
            echo "No submission found for dataset: $messageData->datasetId\n";
            return true;
        }
        if ($datasetSubmission->getDatasetFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_NONE) {
            $this->processDataset($datasetSubmission);
        }
        if ($datasetSubmission->getMetadataFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_NONE) {
            $this->processMetadata($datasetSubmission);
        }
        $this->entityManager->persist($datasetSubmission);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Process the dataset for a dataset submission.
     *
     * @param DatasetSubmission $datasetSubmission The dataset submission to process.
     *
     * @return void
     */
    protected function processDataset(DatasetSubmission $datasetSubmission)
    {
        switch ($datasetSubmission->getDatasetFileTransferType()) {
            case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
            case DatasetSubmission::TRANSFER_TYPE_SFTP:
                if (null === $datasetSubmission->getDatasetFilePath()) {
                    // Log no file path.
                    echo "No dataset file path\n";
                    return;
                }
                try {
                    $this->dataStore->addFile(
                        $datasetSubmission->getDatasetFilePath(),
                        $datasetSubmission->getDataset()->getUdi(),
                        'data'
                    );
                    $datasetSubmission->setDatasetFileTransferStatus(
                        DatasetSubmission::TRANSFER_STATUS_COMPLETED
                    );
                } catch (\Exception $exception) {
                    echo 'Error filing dataset: ' . $exception->getMessage() . "\n";
                }
                // Log processing complete.
                echo "Dataset file processing complete\n";
                // Email user.
                break;
            case DatasetSubmission::TRANSFER_TYPE_HTTP:
                if (null === $datasetSubmission->getDatasetFileUrl()) {
                    // Log no file URL.
                    echo "No dataset file url\n";
                    return;
                }
                // Publish message to retriever queue.
                echo "Publish to retriever queue\n";
                // Email user that data pull has been queued.
                break;
            case null:
                // Transfer type not set.
                echo "Dataset file transfer type not set\n";
                break;
            default:
                // Log unknown transfer type.
                echo 'Unknown dataset file transfer type: ' . $datasetSubmission->getDatasetFileTransferType() . "\n";
        }
    }

    /**
     * Process the metadata for a dataset submission.
     *
     * @param DatasetSubmission $datasetSubmission The dataset submission to process.
     *
     * @return void
     */
    protected function processMetadata(DatasetSubmission $datasetSubmission)
    {
        switch ($datasetSubmission->getMetadataFileTransferType()) {
            case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
            case DatasetSubmission::TRANSFER_TYPE_SFTP:
                if (null === $datasetSubmission->getMetadataFilePath()) {
                    // Log no file path.
                    echo "No metadata file path\n";
                    return;
                }
                try {
                    $this->dataStore->addFile(
                        $datasetSubmission->getMetadataFilePath(),
                        $datasetSubmission->getDataset()->getUdi(),
                        'metadata'
                    );
                    $datasetSubmission->setMetadataFileTransferStatus(
                        DatasetSubmission::TRANSFER_STATUS_COMPLETED
                    );
                } catch (\Exception $exception) {
                    echo 'Error filing metadata: ' . $exception->getMessage() . "\n";
                }
                // Log processing complete.
                echo "Metadata file processing complete\n";
                // Email user.
                break;
            case DatasetSubmission::TRANSFER_TYPE_HTTP:
                if (null === $datasetSubmission->getMetadataFileUrl()) {
                    // Log no file URL.
                    echo "No metadata file url\n";
                    return;
                }
                // Retrieve metadata.
                // Email user that metadata has been retrieved.
                break;
            case null:
                // Transfer type not set.
                echo "Metadata file transfer type not set\n";
                break;
            default:
                // Unknown transfer type.
                echo 'Unknown metadata file transfer type: ' . $datasetSubmission->getMetadataFileTransferType() . "\n";
        }
    }
}
