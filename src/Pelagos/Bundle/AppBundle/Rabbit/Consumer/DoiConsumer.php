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

use Pelagos\Exception\HttpClientErrorException;
use Pelagos\Exception\HttpServerErrorException;

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
     * Delay time in seconds for API.
     */
    const DELAY_TIME = 600;

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
     * @return integer
     */
    public function execute(AMQPMessage $message)
    {
        // @codingStandardsIgnoreStart
        $routingKey = $message->delivery_info['routing_key'];

        $msgStatus = ConsumerInterface::MSG_ACK;

        if (preg_match('/^delete/', $routingKey)) {
            $doi = $message->body;
            $loggingContext = array('doi' => $doi);
            $this->logger->info('DOI Consumer Started', $loggingContext);
            $msgStatus = $this->deleteDoi($doi, $loggingContext);
        } else {
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

            // @codingStandardsIgnoreEnd
            if (preg_match('/^issue/', $routingKey)) {
                $msgStatus = $this->issueDoi($dataset, $loggingContext);
            } elseif (preg_match('/^update/', $routingKey)) {
                $msgStatus = $this->updateDoi($dataset, $loggingContext);
            } else {
                $this->logger->warning("Unknown routing key: $routingKey", $loggingContext);
            }
            $this->entityManager->persist($dataset);
            $this->entityManager->flush();
        }

        return $msgStatus;
    }

    /**
     * Issue a DOI for the dataset.
     *
     * @param Dataset $dataset        The dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return integer
     */
    protected function issueDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to issue DOI', $loggingContext);

        $issueMsg = ConsumerInterface::MSG_ACK;

        if (!$this->doiAlreadyExists($dataset, $loggingContext)) {
            try {
                $doiUtil = new DOIutil();
                $issuedDoi = $doiUtil->mintDOI(
                    'https://data.gulfresearchinitiative.org/tombstone/' . $dataset->getUdi(),
                    $dataset->getAuthors(),
                    $dataset->getTitle(),
                    'Harte Research Institute',
                    $dataset->getReferenceDateYear()
                );

                $doi = new DOI($issuedDoi);
                $doi->setCreator($dataset->getModifier());
                $doi->setModifier($dataset->getModifier());
                $dataset->setDoi($doi);

                $loggingContext['doi'] = $doi->getDoi();
                // Log processing complete.
                $this->logger->info('DOI Issued', $loggingContext);
            } catch (HttpClientErrorException $exception) {
                $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
                $issueMsg = ConsumerInterface::MSG_REJECT;
            } catch (HttpServerErrorException $exception) {
                $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
                //server down. wait for 10 minutes and retry.
                sleep(self::DELAY_TIME);
                $issueMsg = ConsumerInterface::MSG_REJECT_REQUEUE;
            }
        } else {
            $this->logger->warning('The DOI already exist for dataset', $loggingContext);
            $issueMsg = $this->createDoi($dataset, $loggingContext);
        }

        return $issueMsg;
    }

    /**
     * Create a DOI with a known DOI from the dataset.
     *
     * @param Dataset $dataset        The Dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return integer
     */
    private function createDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to create DOI', $loggingContext);

        $createMsg = ConsumerInterface::MSG_ACK;

        $doi = $dataset->getDoi();

        try {
            $doiUtil = new DOIutil();
            $doiUtil->createDOI(
                $doi->getDoi(),
                'https://data.gulfresearchinitiative.org/tombstone/' . $dataset->getUdi(),
                $dataset->getAuthors(),
                $dataset->getTitle(),
                'Harte Research Institute',
                $dataset->getReferenceDateYear()
            );

            $doi->setModifier($dataset->getModifier());

            $loggingContext['doi'] = $doi->getDoi();
            // Log processing complete.
            $this->logger->info('DOI Created', $loggingContext);
        } catch (HttpClientErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            $createMsg = ConsumerInterface::MSG_REJECT;
        } catch (HttpServerErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            //server down. wait for 10 minutes and retry.
            sleep(self::DELAY_TIME);
            $createMsg = ConsumerInterface::MSG_REJECT_REQUEUE;
        }

        return $createMsg;
    }

    /**
     * Update information for the DOI of a dataset.
     *
     * @param Dataset $dataset        The Dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return integer
     */
    protected function updateDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to update DOI', $loggingContext);
        $updateMsg = ConsumerInterface::MSG_ACK;
        $doi = $dataset->getDoi();

        $doiUtil = new DOIutil();

        if (!$this->doiAlreadyExists($dataset, $loggingContext)) {
            $this->issueDoi($dataset, $loggingContext);
        }

        try {
            // Set dataland pages for available datasets and tombstone pages for unavailable datasets.
            if (($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE) or
                ($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)) {
                $doiUrl = 'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi();
                $doi->setPublicDate(new \DateTime);
            } else {
                $doiUrl = 'https://data.gulfresearchinitiative.org/tombstone/' . $dataset->getUdi();
            }

            $creator = ($dataset->getAuthors()) ? $dataset->getAuthors() : '(:tba)';

            // PublicationYear field can not be null, as it is a required field when the DOI is published
            $pubYear = $dataset->getReferenceDateYear();
            if (empty($pubYear) and
                $dataset->getDif()->getApprovedDate() instanceof \Datetime) {
                $pubYear = $dataset->getDif()->getApprovedDate()->format('Y');
            }
            $doiUtil->updateDOI(
                $doi->getDoi(),
                $doiUrl,
                $creator,
                $dataset->getTitle(),
                'Harte Research Institute',
                $pubYear
            );

            $doi->setModifier($dataset->getModifier());
            $doi->setStatus('public');
            $doi->setModifier($dataset->getModifier());

            $loggingContext['doi'] = $doi->getDoi();

            // Log processing complete.
            $this->logger->info('DOI Updated', $loggingContext);
        } catch (HttpClientErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            $updateMsg = ConsumerInterface::MSG_REJECT;
        } catch (HttpServerErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            //server down. wait for 10 minutes and retry.
            sleep(self::DELAY_TIME);
            $updateMsg = ConsumerInterface::MSG_REJECT_REQUEUE;
        }

        return $updateMsg;
    }

    /**
     * Delete the doi if dataset is deleted.
     *
     * @param string $doi            The DOI which needs to be deleted.
     * @param array  $loggingContext The logging context to use when logging.
     *
     * @return integer
     */
    protected function deleteDoi($doi, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to delete DOI', $loggingContext);
        $deleteMsg = ConsumerInterface::MSG_ACK;
        try {
            $doiUtil = new DOIutil();
            $doiUtil->deleteDOI($doi);
        } catch (HttpClientErrorException $exception) {
            $this->logger->error('Error deleting DOI: ' . $exception->getMessage(), $loggingContext);
            $deleteMsg = ConsumerInterface::MSG_REJECT;
        } catch (HttpServerErrorException $exception) {
            $this->logger->error('Error deleting DOI: ' . $exception->getMessage(), $loggingContext);
            //server down. wait for 10 minutes and retry.
            sleep(self::DELAY_TIME);
            $deleteMsg = ConsumerInterface::MSG_REJECT_REQUEUE;
        }

        return $deleteMsg;
    }

    /**
     * Check if DOI already exists.
     *
     * @param Dataset $dataset        The dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return boolean
     */
    private function doiAlreadyExists(Dataset $dataset, $loggingContext): bool
    {
        $doi = $dataset->getDoi();
        $exceptionType = null;

        if ($doi instanceof DOI) {
            do {
                try {
                    $doiUtil = new DOIutil();
                    $doiUtil->getDOIMetadata($doi->getDoi());
                } catch (HttpClientErrorException $exception) {
                    //DOI exist, but is not found in EZID/Datacite.
                    $this->logger->error('Error getting DOI: ' . $exception->getMessage(), $loggingContext);
                    $exceptionType = get_class($exception);
                    $this->createDoi($dataset, $loggingContext);
                } catch (HttpServerErrorException $exception) {
                    //server down. wait for 10 minutes and retry.
                    $this->logger->error('Error getting DOI: ' . $exception->getMessage(), $loggingContext);
                    sleep(self::DELAY_TIME);
                    $exceptionType = get_class($exception);
                    continue;
                }
            } while ($exceptionType === HttpServerErrorException::class);

            return true;
        }
        return false;
    }
}
