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

        $msgStatus = self::MSG_ACK;

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

        $issueMsg = self::MSG_ACK;

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
                $issueMsg = self::MSG_REJECT;
            } catch (HttpServerErrorException $exception) {
                $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
                $issueMsg = self::MSG_REJECT_REQUEUE;
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
     * @return boolean True if success, false otherwise.
     */
    private function createDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to create DOI', $loggingContext);

        $createMsg = self::MSG_ACK;

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
            $createMsg = self::MSG_REJECT;
        } catch (HttpServerErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            $createMsg = self::MSG_REJECT_REQUEUE;
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
        $updateMsg = self::MSG_ACK;
        $doi = $dataset->getDoi();

        $doiUtil = new DOIutil();

        if (!$this->doiAlreadyExists($dataset, $loggingContext)) {
            $this->issueDoi($dataset, $loggingContext);
        }

        try {
            //Getting the DOI status from the EZ ID API
            $doiMetaData = $doiUtil->getDOIMetadata($doi->getDoi());
            $doiStatus = $doiMetaData['_status'];
            $status = $this->getDoiStatus($dataset, $doiStatus);
            if ($status === DOI::STATUS_PUBLIC) {
                $doiUrl = 'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi();
                $doi->setPublicDate(new \DateTime);
            } else {
                $doiUrl = 'https://data.gulfresearchinitiative.org/tombstone/' . $dataset->getUdi();
            }

            $creator = ($dataset->getAuthors()) ? $dataset->getAuthors() : '(:tba)';
            $doiUtil->updateDOI(
                $doi->getDoi(),
                $doiUrl,
                $creator,
                $dataset->getTitle(),
                'Harte Research Institute',
                $dataset->getReferenceDateYear(),
                $status
            );

            $doi->setModifier($dataset->getModifier());
            $doi->setStatus($status);
            $doi->setModifier($dataset->getModifier());

            $loggingContext['doi'] = $doi->getDoi();

            // Log processing complete.
            $this->logger->info('DOI Updated', $loggingContext);
            $this->logger->info('DOI set to status: ' . $status, $loggingContext);
        } catch (HttpClientErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            $updateMsg = self::MSG_REJECT;
        } catch (HttpServerErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            $updateMsg = self::MSG_REJECT_REQUEUE;
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
        $deleteMsg = self::MSG_ACK;
        try {
            $doiUtil = new DOIutil();
            $doiUtil->deleteDOI($doi);
        } catch (HttpClientErrorException $exception) {
            $this->logger->error('Error deleting DOI: ' . $exception->getMessage(), $loggingContext);
            $deleteMsg = self::MSG_REJECT;
        } catch (HttpServerErrorException $exception) {
            $this->logger->error('Error deleting DOI: ' . $exception->getMessage(), $loggingContext);
            $deleteMsg = self::MSG_REJECT_REQUEUE;
        }

        return $deleteMsg;
    }

    /**
     * Get the Doi status to be persisted.
     *
     * @param Dataset $dataset   The dataset.
     * @param string  $doiStatus The status of the DOI for the dataset.
     *
     * @return string
     */
    private function getDoiStatus($dataset, $doiStatus)
    {
        //declaring it as reserved for defensive purpose
        $status = DOI::STATUS_RESERVED;

        $restriction = DatasetSubmission::RESTRICTION_NONE;

        if ($dataset->getDatasetSubmission() instanceof DatasetSubmission) {
            $restriction = $dataset->getDatasetSubmission()->getRestrictions();
        }

        if ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_ACCEPTED and
            $doiStatus === DOI::STATUS_PUBLIC ) {
            $status = DOI::STATUS_UNAVAILABLE;
        } elseif ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED and
            $doiStatus === DOI::STATUS_PUBLIC and $restriction === DatasetSubmission::RESTRICTION_RESTRICTED) {
            $status = DOI::STATUS_UNAVAILABLE;
        } elseif ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED
            and $restriction === DatasetSubmission::RESTRICTION_NONE) {
            $status = DOI::STATUS_PUBLIC;
        }

        return $status;
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

        if ($doi instanceof DOI) {
            try {
                $doiUtil = new DOIutil();
                $doiUtil->getDOIMetadata($doi->getDoi());
            } catch (\Exception $exception) {
                //DOI exist, but is not found in EZID/Datacite.
                $this->createDoi($dataset, $loggingContext);
            }
            return true;
        }
        return false;
    }
}
