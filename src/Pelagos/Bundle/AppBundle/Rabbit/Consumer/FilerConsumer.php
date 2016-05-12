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
        $data = json_decode($message->body);
        // Clear Doctrine's cache to force loading from persistence.
        $this->entityManager->clear();
        $dataset = $this->entityManager
                        ->getRepository(Dataset::class)
                        ->find($data->datasetId);
        if ($dataset instanceof Dataset) {
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                if ($datasetSubmission->getDatasetFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_NONE) {
                    try {
                        $this->dataStore->addFile(
                            $datasetSubmission->getDatasetFilePath(),
                            $datasetSubmission->getDataset()->getUdi(),
                            'data'
                        );
                        $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
                    } catch (\Exception $exception) {
                        echo 'Error filing dataset: ' . $exception->getMessage() . "\n";
                    }
                }
                if ($datasetSubmission->getMetadataFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_NONE) {
                    try {
                        $this->dataStore->addFile(
                            $datasetSubmission->getMetadataFilePath(),
                            $datasetSubmission->getDataset()->getUdi(),
                            'metadata'
                        );
                        $datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_COMPLETED);
                    } catch (\Exception $exception) {
                        echo 'Error filing metadata: ' . $exception->getMessage() . "\n";
                    }
                }
                $this->entityManager->persist($datasetSubmission);
                $this->entityManager->flush();
            }
        }
        return true;
    }
}
