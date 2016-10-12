<?php


namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Doctrine\ORM\EntityManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bridge\Monolog\Logger;
use Pelagos\Util\DataStore;

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
        
        // find the Dataset instance that has this ID (udi)
        $datasetId = $message->body;
        $loggingContext = array('dataset_id' => $datasetId);
        $this->logger->info('Dataset File Hash start', $loggingContext);
        // Clear Doctrine's cache to force loading from persistence.
        $this->entityManager->clear();
        $dataset = $this->entityManager
                        ->getRepository(Dataset::class)
                        ->find($datasetId);
        if (!$dataset instanceof Dataset) {
            $this->logger->warning('No dataset found', $loggingContext);
            return true;
        }
        $datasetUdi = $dataset->getUdi();
        if (null !== $datasetUdi) {
            $loggingContext['udi'] = $datasetUdi;
        }
        // get the DatasetSubmission referenced in the found Dataset.
        // This is the instance to which this code writes the calculated
        // SHA256 hash value
        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!$datasetSubmission instanceof DatasetSubmission) {
            $this->logger->warning('No submission found for dataset', $loggingContext);
            return true;
        }
        
        try {
            $datasetFileInfo = $this->dataStore->getFileInfo($datasetUdi, DataStore::DATASET_FILE_TYPE);
            $fileName = $datasetFileInfo->getFilename();
            $hexDigits = hash_file(DatasetSubmission::SHA256, $fileName);
            $datasetSubmission->setDatasetFileSha256Hash($hexDigits);
            $loggingContext['dataset_submission_id'] = $datasetSubmission->getId();
        } catch (FileNotFoundException ex) {
                 $this->logger->warning('Can not hash file: ' . $fileName . '. File does not exist ', $loggingContext);
                 return true;
        }
        $this->entityManager->persist($datasetSubmission);
        $this->entityManager->flush();
        $this->logger->info('Dataset File Hash end', $loggingContext);
        return true;
    }
}
