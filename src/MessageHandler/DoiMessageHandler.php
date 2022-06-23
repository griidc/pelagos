<?php

namespace App\MessageHandler;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DOI;
use App\Exception\HttpClientErrorException;
use App\Exception\HttpServerErrorException;
use App\Message\DoiMessage;
use App\Util\DOIutil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DoiMessageHandler implements MessageHandlerInterface
{
    /**
     * The Entity Manager.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Utility class for DOI.
     *
     * @var DOIutil
     */
    protected $doiUtil;

    /**
     * DoiMessageHandler constructor.
     *
     * @param EntityManagerInterface $entityManager  The entity handler.
     * @param LoggerInterface        $doiIssueLogger Name hinted doi_issue logger.
     * @param DOIutil                $doiUtil               Instance of Utility class DOI.
     *
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $doiIssueLogger,
        DOIutil $doiUtil
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $doiIssueLogger;
        $this->doiUtil = $doiUtil;
    }

    /**
     * Invoke function to process a doi.
     *
     * @param DoiMessage $doiMessage The Doi Message that has to be handled.
     */
    public function __invoke(DoiMessage $doiMessage)
    {
        $doiMessageId = $doiMessage->getContextId();

        $doiMessageAction = $doiMessage->getAction();

        if ($doiMessageAction === DoiMessage::DELETE_ACTION) {
            $loggingContext = array('doi' => $doiMessageId);
            $this->logger->info('DOI Consumer Started', $loggingContext);
            $this->deleteDoi($doiMessageId, $loggingContext);
        } elseif ($doiMessageAction === DoiMessage::ISSUE_OR_UPDATE) {
            $loggingContext = array('dataset_id' => $doiMessageId);
            $this->logger->info('DOI Consumer Started', $loggingContext);
            // Clear Doctrine's cache to force loading from persistence.
            $dataset = $this->entityManager
                ->getRepository(Dataset::class)
                ->find($doiMessageId);
            if (!$dataset instanceof Dataset) {
                $this->logger->warning('No dataset found', $loggingContext);
                return;
            }
            if (null !== $dataset->getUdi()) {
                $loggingContext['udi'] = $dataset->getUdi();
            }
            if ($this->doiAlreadyExists($dataset, $loggingContext)) {
                $this->logger->info('DOI Already issued for this dataset', $loggingContext);
                $this->updateDoi($dataset, $loggingContext);
            } else {
                $this->issueDoi($dataset, $loggingContext);
            }

            $this->entityManager->flush();
        } else {
            $this->logger->warning("Unknown message action: $doiMessageAction");
        }
    }

    /**
     * Issue a DOI for the dataset.
     *
     * @param Dataset $dataset        The dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return void
     */
    protected function issueDoi(Dataset $dataset, array $loggingContext): void
    {
        // Log processing start.
        $this->logger->info('Attempting to issue DOI', $loggingContext);

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
        } catch (HttpClientErrorException | HttpServerErrorException $exception) {
            $this->logger->error('Error issuing DOI: ' . $exception->getMessage(), $loggingContext);
        }
    }

    /**
     * Update information for the DOI of a dataset.
     *
     * @param Dataset $dataset        The Dataset.
     * @param array   $loggingContext The logging context to use when logging.
     *
     * @return void
     */
    protected function updateDoi(Dataset $dataset, array $loggingContext): void
    {
        // Log processing start.
        $this->logger->info('Attempting to update DOI', $loggingContext);
        $doi = $dataset->getDoi();

        try {
            // Set dataland pages for available datasets and tombstone pages for unavailable datasets.
            if (
                ($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE) or
                ($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)
            ) {
                $doiUrl = 'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi();
                $doi->setPublicDate(new \DateTime());
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
        } catch (HttpClientErrorException | HttpServerErrorException $exception) {
            $this->logger->error('Error requesting DOI: ' . $exception->getMessage(), $loggingContext);
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
    protected function deleteDoi(string $doi, array $loggingContext): void
    {
        // Log processing start.
        $this->logger->info('Attempting to delete DOI', $loggingContext);
        try {
            $this->doiUtil->deleteDOI($doi);
        } catch (HttpClientErrorException | HttpServerErrorException $exception) {
            $this->logger->error('Error deleting DOI: ' . $exception->getMessage(), $loggingContext);
        }
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
                    $exceptionType = get_class($exception);
                    continue;
                }
            } while ($exceptionType === HttpServerErrorException::class);

            return true;
        }
        return false;
    }
}
