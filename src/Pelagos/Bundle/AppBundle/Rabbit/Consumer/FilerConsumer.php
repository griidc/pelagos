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
        try {
            $this->dataStore->addFile(
                $datasetSubmission->getDatasetFileUri(),
                $datasetSubmission->getDataset()->getUdi(),
                'dataset'
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
        try {
            $this->dataStore->addFile(
                $datasetSubmission->getMetadataFileUri(),
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
    }
}
