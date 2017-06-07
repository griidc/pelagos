<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Doctrine\ORM\EntityManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use PhpAmqpLib\Message\AMQPMessage;

use Symfony\Bridge\Monolog\Logger;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DOI;

use Pelagos\Event\EntityEventDispatcher;

use Pelagos\Util\DOIutil;

/**
 * A consumer of DOI messages.
 *
 * @see ConsumerInterface
 */
class DoiConsumer implements ConsumerInterface
{
    /**
     * The entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

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
     * Constructor.
     *
     * @param EntityManager         $entityManager         The entity manager.
     * @param Logger                $logger                A Monolog logger.
     * @param EntityEventDispatcher $entityEventDispatcher The entity event dispatcher.
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        EntityEventDispatcher $entityEventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->entityEventDispatcher = $entityEventDispatcher;
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
        $this->logger->info('DOI Consumer Started', $loggingContext);
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

        // @codingStandardsIgnoreStart
        $routingKey = $message->delivery_info['routing_key'];
        // @codingStandardsIgnoreEnd
        if (preg_match('/^issue/', $routingKey)) {
            $this->issueDoi($dataset, $loggingContext);
        } elseif (preg_match('/^publish/', $routingKey)) {
            //$this->publishDoi($dataset, $loggingContext);
        } elseif (preg_match('/^update/', $routingKey)) {
            $this->updateDoi($dataset, $loggingContext);
        } else {
            $this->logger->warning("Unknown routing key: $routingKey", $loggingContext);
            return true;
        }
        $this->entityManager->persist($dataset);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Issue a DOI for the dataset.
     *
     * @param Dataset $dataset        The dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return boolean True if success, false otherwise.
     */
    protected function issueDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to issue DOI', $loggingContext);

        $doi = $dataset->getDoi();
        if ($doi instanceof DOI) {
            $this->logger->warning('The DOI already exist for dataset', $loggingContext);
            //Update a the DOI instead.
            $this->updateDoi($dataset, $loggingContext);
            return true;
        }
        
        try {
            $doiUtil = new DOIutil();
            $issuedDoi = $doiUtil->createDOI(
                'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi(),
                $dataset->getAuthors(),
                $dataset->getTitle(),
                'Harte Research Institute',
                $dataset->getReferenceDateYear()
            );
        } catch (\Exception $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            return;
        }

        $doi = new DOI($issuedDoi);
        $dataset->setDoi($doi);

        $loggingContext['doi'] = $doi;
        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($dataset, 'doi_issued');
        // Log processing complete.
        $this->logger->info('DOI Issued', $loggingContext);

        return true;
    }

    /**
     * Update information for the DOI of a dataset.
     *
     * @param Dataset $dataset        The Dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return boolean True if success, false otherwise.
     */
    protected function updateDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to update DOI', $loggingContext);

        $doi = $dataset->getDoi();
        if (!$doi instanceof DOI) {
            $this->logger->warning('No DOI found for dataset', $loggingContext);
            //Create a new DOI instead.
            $this->issueDoi($dataset, $loggingContext);
            return true;
        }

        try {
            $doiUtil = new DOIutil();
            $issuedDoi = $doiUtil->updateDOI(
                $doi->getDoi(),
                'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi(),
                $dataset->getAuthors(),
                $dataset->getTitle(),
                'Harte Research Institute',
                $dataset->getReferenceDateYear()
            );
        } catch (\Exception $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            return;
        }

        $loggingContext['doi'] = $doi;
        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($dataset, 'doi_updated');
        // Log processing complete.
        $this->logger->info('DOI Updated', $loggingContext);
    }
}
