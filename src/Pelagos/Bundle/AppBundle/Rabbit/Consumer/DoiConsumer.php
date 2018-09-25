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
        // @codingStandardsIgnoreStart
        $routingKey = $message->delivery_info['routing_key'];

        if (preg_match('/^delete/', $routingKey)) {
            $doi = $message->body;
            $loggingContext = array('doi' => $doi);
            $this->logger->info('DOI Consumer Started', $loggingContext);
            $this->deleteDoi($doi, $loggingContext);
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
                $this->issueDoi($dataset, $loggingContext);
            } elseif (preg_match('/^publish/', $routingKey)) {
                $this->publishDoi($dataset, $loggingContext);
            } elseif (preg_match('/^update/', $routingKey)) {
                $this->updateDoi($dataset, $loggingContext);
            } else {
                $this->logger->warning("Unknown routing key: $routingKey", $loggingContext);
                return true;
            }
            $this->entityManager->persist($dataset);
            $this->entityManager->flush();
        }

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

        $doiUtil = new DOIutil();

        $doi = $dataset->getDoi();

        if ($doi instanceof DOI) {
            $this->logger->warning('The DOI already exist for dataset', $loggingContext);
            $doiId = $doi->getDoi();
            try {
                $doiMetaData = $doiUtil->getDOIMetadata($doiId);
            } catch (\Exception $exception) {
                //DOI exist, but is not found in EZID/Datacite.
                $this->logger->warning('No DOI metadata found, so create the DOI for dataset', $loggingContext);
                return $this->createDoi($dataset, $loggingContext);
            }
            //Update a the DOI instead.
            $this->logger->info('DOI found, updating the DOI for dataset', $loggingContext);
            return $this->updateDoi($dataset, $loggingContext);
        }

        try {
            $issuedDoi = $doiUtil->mintDOI(
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
        $doi->setCreator($dataset->getModifier());
        $doi->setModifier($dataset->getModifier());
        $dataset->setDoi($doi);

        $loggingContext['doi'] = $doi->getDoi();
        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($dataset, 'doi_issued');
        // Log processing complete.
        $this->logger->info('DOI Issued', $loggingContext);

        return true;
    }

    /**
     * Create a DOI with a known DOI from the dataset.
     *
     * @param Dataset $dataset        The Dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return boolean True if success, false otherwise.
     */
    protected function createDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to create DOI', $loggingContext);

        $doi = $dataset->getDoi();
        if (!$doi instanceof DOI) {
            // If we can't find a DOI for the dataset, a DOI can not be created.
            $this->logger->error('No DOI was found to create.', $loggingContext);
            return false;
        }

        try {
            $doiUtil = new DOIutil();
            $success = $doiUtil->createDOI(
                $doi->getDoi(),
                'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi(),
                $dataset->getAuthors(),
                $dataset->getTitle(),
                'Harte Research Institute',
                $dataset->getReferenceDateYear()
            );
        } catch (\Exception $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
            return false;
        }

        $doi->setModifier($dataset->getModifier());

        $loggingContext['doi'] = $doi->getDoi();
        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($dataset, 'doi_created');
        // Log processing complete.
        $this->logger->info('DOI Created', $loggingContext);

        return $success;
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

        $doiUtil = new DOIutil();

        try {
            $doiId = $doi->getDoi();
            $doiMetaData = $doiUtil->getDOIMetadata($doiId);
        } catch (\Exception $exception) {
            //DOI exist, but is not found in EZID/Datacite.
            $this->logger->warning('No DOI metadata found, so create the DOI for dataset', $loggingContext);
            return $this->createDoi($dataset, $loggingContext);
        }

        try {
            $success = $doiUtil->updateDOI(
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

        $doi->setModifier($dataset->getModifier());

        $loggingContext['doi'] = $doi->getDoi();
        // Dispatch entity event.
        $this->entityEventDispatcher->dispatch($dataset, 'doi_updated');
        // Log processing complete.
        $this->logger->info('DOI Updated', $loggingContext);

        return $success;
    }

    /**
     * Mark a DOI public (published) for a dataset.
     *
     * @param Dataset $dataset        The dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return boolean True if success, false otherwise.
     */
    protected function publishDoi(Dataset $dataset, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to mark DOI as published', $loggingContext);

        $doi = $dataset->getDoi();
        if (!$doi instanceof DOI) {
            $this->logger->error('No DOI found for dataset', $loggingContext);
            return true;
        }
        $doiId = $doi->getDoi();

        $loggingContext['doi'] = $doiId;

        try {
            $doiUtil = new DOIutil();

            //Getting the DOI status from the EZ ID API
            $doiMetaData = $doiUtil->getDOIMetadata($doiId);
            $doiStatus = $doiMetaData['_status'];

            if ($this->validatePublish($dataset, $doiStatus, $loggingContext)) {
                $status = $this->getDoiStatus($dataset, $doiStatus);

                try {
                    $doiUtil->publishDOI($doiId, $status);
                } catch (\Exception $exception) {
                    $this->logger->error('Error setting DOI to published: ' . $exception->getMessage(), $loggingContext);
                    return;
                }

                $doi->setStatus($status);
                $doi->setPublicDate(new \DateTime);
                $doi->setModifier($dataset->getModifier());

                // Dispatch entity event.
                $this->entityEventDispatcher->dispatch($dataset, 'doi_published');
                // Log processing complete.
                $this->logger->info('DOI set to published', $loggingContext);
            } else {
                $this->logger->info('DOI already in the right status, No action is taken', $loggingContext);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error getting DOI metadata: ' . $e->getMessage(), $loggingContext);
        }
    }

    /**
     * Delete the doi if dataset is deleted.
     *
     * @param string $doi            The DOI which needs to be deleted.
     * @param array  $loggingContext The logging context to use when logging.
     *
     * @return void
     */
    protected function deleteDoi($doi, array $loggingContext)
    {
        // Log processing start.
        $this->logger->info('Attempting to delete DOI', $loggingContext);

        try {
            $doiUtil = new DOIutil();
            $doiUtil->deleteDOI($doi);
        } catch (\Exception $exception) {
            $this->logger->error('Error deleting DOI: ' . $exception->getMessage(), $loggingContext);
            return;
        }

        $this->logger->info('DOI Deleted', $loggingContext);
    }

    /**
     * Validate publish for DOI for the dataset.
     *
     * @param Dataset $dataset        The dataset.
     * @param string  $doiStatus      The status of the DOI for the dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return boolean
     */
    private function validatePublish($dataset, $doiStatus, array $loggingContext)
    {
        if ($dataset->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED and
            $doiStatus === DOI::STATUS_PUBLIC and
            $dataset->getDatasetSubmission()->getRestrictions() === DatasetSubmission::RESTRICTION_NONE) {
            // Don't attempt to publish an already published DOI to preserve the original pub date.
            $this->logger->warning('DOI for dataset already marked as published', $loggingContext);
            return false;
        } elseif ($dataset->getMetadataStatus() !== DatasetSubmission::METADATA_STATUS_ACCEPTED and
            $doiStatus === DOI::STATUS_RESERVED) {
            // Don't attempt to publish not accepted dataset.
            $this->logger->warning('DOI for dataset is not accepted, No action is taken', $loggingContext);
            return false;
        }

        return true;
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

        $restriction = $dataset->getDatasetSubmission()->getRestrictions();

        if ($dataset->getMetadataStatus() !== DatasetSubmission::METADATA_STATUS_ACCEPTED and
            $doiStatus === DOI::STATUS_PUBLIC ) {
            $status = DOI::STATUS_UNAVAILABLE;
        } elseif ($dataset->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED and
            $doiStatus === DOI::STATUS_PUBLIC and $restriction === DatasetSubmission::RESTRICTION_RESTRICTED) {
            $status = DOI::STATUS_UNAVAILABLE;
        } elseif ($dataset->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED and
            in_array($doiStatus, [DOI::STATUS_RESERVED, DOI::STATUS_UNAVAILABLE]) and
            $restriction === DatasetSubmission::RESTRICTION_NONE) {
            $status = DOI::STATUS_PUBLIC;
        }

        return $status;
    }
}
