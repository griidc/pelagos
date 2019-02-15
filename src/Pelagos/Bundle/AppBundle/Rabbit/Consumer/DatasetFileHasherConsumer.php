<?php


namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Doctrine\ORM\EntityManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bridge\Monolog\Logger;
use Pelagos\Util\DataStore;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Dataset;

/**
 * A consumer of dataset hash file request messages.
 *
 * Calculate a SHA256 hash of the dataset file and store it
 * in the DatasetSubmission datasetFileSha256Hash attribute.
 *
 * @see ConsumerInterface
 */
class DatasetFileHasherConsumer implements ConsumerInterface
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
     * Constructor.
     *
     * @param EntityManager $entityManager The entity manager.
     * @param DataStore     $dataStore     The data store service.
     * @param Logger        $logger        A Monolog logger.
     */
    public function __construct(
        EntityManager $entityManager,
        DataStore $dataStore,
        Logger $logger
    ) {
        $this->entityManager = $entityManager;
        $this->dataStore = $dataStore;
        $this->logger = $logger;
    }

   /**
    * Process a hash_file message.
    *
    * Create a hash of the data file
    *
    * @param AMQPMessage $message A hash_file message.
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
        $loggingContext = array('dataset_id' => $dataset->getId());
        $this->logger->info('Dataset File Hash start', $loggingContext);

        if (!$dataset instanceof Dataset) {
            $this->logger->warning('No dataset found', $loggingContext);
            return true;
        }
        $datasetUdi = $dataset->getUdi();
        if (null !== $datasetUdi) {
            $loggingContext['udi'] = $datasetUdi;
        }

        if (!$datasetSubmission instanceof DatasetSubmission) {
            $this->logger->warning('No submission found for dataset', $loggingContext);
            return true;
        }
        
        $loggingContext['dataset_submission_id'] = $datasetSubmission->getId();
        try {
            $datasetFileInfo = $this->dataStore->getFileInfo($datasetUdi, DataStore::DATASET_FILE_TYPE);
        } catch (FileNotFoundException $ex) {
            $this->logger->warning('Can not hash dataset file. File not found ', $loggingContext);
            $this->logger->warning('URI: ' . $datasetSubmission->getDatasetFileUri(), $loggingContext);
            $this->logger->warning($this->dataStore->getFileInfo($datasetUdi, DataStore::DATASET_FILE_TYPE), $loggingContext);
            return true;
        }
        $filePath = $datasetFileInfo->getRealPath();
        $hexDigits = hash_file(DatasetSubmission::SHA256, $filePath);
        $datasetSubmission->setDatasetFileSha256Hash($hexDigits);
        $this->entityManager->persist($datasetSubmission);
        $this->entityManager->flush();
        $this->logger->info('Dataset File Hash end', $loggingContext);
        return true;
    }
}
