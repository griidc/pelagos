<?php

namespace App\Consumer;

use App\Entity\DatasetPublication;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

use PhpAmqpLib\Message\AMQPMessage;

use Symfony\Bridge\Monolog\Logger;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DOI;

use App\Event\EntityEventDispatcher;

use App\Util\DOIutil;

use App\Exception\HttpClientErrorException;
use App\Exception\HttpServerErrorException;

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
     * @var EntityManagerInterface
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
     * Utility class for DOI.
     *
     * @var DOIutil
     */
    protected $doiUtil;

    /**
     * Delay time in seconds for API.
     */
    const DELAY_TIME = 600;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager         The entity manager.
     * @param Logger                 $logger                A Monolog logger.
     * @param EntityEventDispatcher  $entityEventDispatcher The entity event dispatcher.
     * @param DOIutil                $doiUtil               Instance of Utility class DOI.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Logger $logger,
        EntityEventDispatcher $entityEventDispatcher,
        DOIutil $doiUtil
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->doiUtil = $doiUtil;
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
        $this->logger->info('Request received, waiting...');

        sleep(6);

        // phpcs:disable
        $routingKey = $message->delivery_info['routing_key'];
        // phpcs:enable
        $msgStatus = ConsumerInterface::MSG_ACK;

        if (preg_match('/^delete/', $routingKey)) {
            $doi = $message->body;
            $loggingContext = array('doi' => $doi);
            $this->logger->info('DOI Consumer Started', $loggingContext);
            $msgStatus = $this->deleteDoi($doi, $loggingContext);
        } elseif (preg_match('/^doi/', $routingKey)) {
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
            if ($this->doiAlreadyExists($dataset, $loggingContext)) {
                $this->logger->info('DOI Already issued for this dataset', $loggingContext);
                $msgStatus = $this->updateDoi($dataset, $loggingContext);
            } else {
                $msgStatus = $this->issueDoi($dataset, $loggingContext);
            }

            $this->entityManager->persist($dataset);
            $this->entityManager->flush();
        } else {
            $this->logger->warning("Unknown routing key: $routingKey");
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

        $generatedDOI = $this->doiUtil->generateDoi();

        // Just to cover an odd edge case
        $pubYear = ($dataset->getAcceptedDate() instanceof \DateTime) ? $dataset->getAcceptedDate()->format('Y') : null;

        try {
            $this->doiUtil->createDOI(
                $generatedDOI,
                'https://data.gulfresearchinitiative.org/tombstone/' . $dataset->getUdi(),
                $dataset->getAuthors(),
                $dataset->getTitle(),
                $pubYear,
                'Harte Research Institute'
            );

            $doi = new DOI($generatedDOI);
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

        return $issueMsg;
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

        try {
            // Set dataland pages for available datasets and tombstone pages for unavailable datasets.
            if (($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE) or
                ($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)) {
                $doiUrl = 'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi();
                $doi->setPublicDate(new \DateTime);
            } else {
                $doiUrl = 'https://data.gulfresearchinitiative.org/tombstone/' . $dataset->getUdi();
            }

            $loggingContext['availabilitystatus'] = $dataset->getAvailabilityStatus();

            $creator = ($dataset->getAuthors()) ? $dataset->getAuthors() : '(:tba)';

            // PublicationYear field can not be null, as it is a required field when the DOI is published
            $pubYear = '';
            $acceptedDate = $dataset->getAcceptedDate();
            if ($acceptedDate instanceof \DateTime) {
                $pubYear = $acceptedDate->format('Y');
            } else {
                $difApprovedDate = $dataset->getDif()->getApprovedDate();
                if ($difApprovedDate instanceof \Datetime) {
                    $pubYear = $dataset->getDif()->getApprovedDate()->format('Y');
                }
            }
            $publicationDois = [];
            $datasetPublications = $dataset->getPublications();
            foreach ($datasetPublications as $datasetPublication) {
                $publicationDois[] = $datasetPublication->getDoi();
            }

            $this->doiUtil->updateDOI(
                $doi->getDoi(),
                $doiUrl,
                $creator,
                $dataset->getTitle(),
                $pubYear,
                'Harte Research Institute',
                $publicationDois
            );

            $loggingContext['update-data'] = array(
                'title' => $dataset->getTitle(),
                'url' => $doiUrl,
                'creator' => $creator,
                'year' => $pubYear,
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
    protected function deleteDoi(string $doi, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to delete DOI', $loggingContext);
        $deleteMsg = ConsumerInterface::MSG_ACK;
        try {
            $this->doiUtil->deleteDOI($doi);
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
    private function doiAlreadyExists(Dataset $dataset, array $loggingContext): bool
    {
        $doi = $dataset->getDoi();
        $exceptionType = null;

        if ($doi instanceof DOI) {
            do {
                try {
                    $this->doiUtil->getDOIMetadata($doi->getDoi());
                } catch (HttpClientErrorException $exception) {
                    //DOI exist, but is not found in REST API/Datacite.
                    $this->logger->error('Error getting DOI: ' . $exception->getMessage(), $loggingContext);
                    return false;
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
