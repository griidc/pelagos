<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Entity\DOI;
use App\Util\DOIutil;
use App\Util\MailSender;
use Twig\Environment;

/**
 * This Symfony Command compares dois between griidc and datacite.
 *
 * @see Command
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:dataset-doi:comparison', description: 'DOI comparison tool.')]
class DoiComparisonCommand extends Command
{
    /**
     * A value for doi state from Datacite.
     */
    const DOI_FINDABLE = 'findable';

    /**
     * A value for doi state from Datacite.
     */
    const DOI_DRAFT = 'draft';

    /**
     * A value for doi state from Datacite.
     */
    const DOI_REGISTERED = 'registered';

    /**
     * Array of out of sync dois.
     *
     * @var array
     */
    protected $outOfSyncDoi = [];

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Custom swiftmailer instance.
     *
     * @var MailSender
     */
    protected $mailer;

    /**
     * DOI Utility class.
     *
     * @var DOIutil
     */
    protected $doiUtil;

    /**
     * Twig environment instance.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param DOIutil                $doiUtil       Doi utility class instance.
     * @param MailSender             $mailer        Custom swiftmailer instance.
     * @param Environment            $twig          Twig environment variable.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DOIutil $doiUtil,
        MailSender $mailer,
        Environment $twig
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->doiUtil = $doiUtil;
        $this->twig = $twig;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'emailRecipientList',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Email recipient list for Doi comparison report'
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $emailRecipientList = $input->getArgument('emailRecipientList');
        $doiJson = [];
        $doiData = [];
        $pageNumber = 1;

        do {
            $body = $this->doiUtil->getDoiCollection($pageNumber);
            $doiJson[$pageNumber] = $body['data'];
            $pageNumber++;
        } while (array_key_exists('next', $body['links']));

        foreach ($doiJson as $dois) {
            foreach ($dois as $doi) {
                $doiData[$doi['id']] = ['doi' => $doi['attributes']['doi'], 'url' => $doi['attributes']['url'], 'udi' => $this->getUdi($doi['attributes']['url']), 'title' => str_replace(
                    ',',
                    '',
                    $this->doesKeyExist($doi['attributes']['titles'], 'title')
                        ? $doi['attributes']['titles'][0]['title'] : ''
                ), 'author' => str_replace(
                    ',',
                    '',
                    $this->doesKeyExist($doi['attributes']['creators'], 'name')
                        ? $doi['attributes']['creators'][0]['name'] : ''
                ), 'publisher' => $doi['attributes']['publisher'], 'state' => $doi['attributes']['state'], 'resourceType' => $this->getResourceType($doi['attributes']['types'])];
            }
        }

        $this->syncConditions($doiData, $emailRecipientList);
        return Command::SUCCESS;
    }

    /**
     * Get udi from Url.
     *
     * @param string $url Url that needs to be fetched.
     */
    private function getUdi(string $url): ?string
    {
        $udi = null;
        $udiRegEx = '/\b([A-Z\d]{2}\.x\d\d\d\.\d\d\d:\d\d\d\d)\b/';
        if (preg_match_all($udiRegEx, $url, $matches)) {
            trim((string) preg_replace($udiRegEx, '', $url));
            $udi = $matches[1][0];
        }

        return $udi;
    }

    /**
     * Get the resource type for the Doi.
     *
     * @param array $types Types of resources from doi.
     */
    private function getResourceType(array $types): string
    {
        $resourceType = '';
        if (array_key_exists('resourceTypeGeneral', $types)) {
            $resourceType = $types['resourceTypeGeneral'];
        } elseif (array_key_exists('resourceType', $types)) {
            $resourceType = $types['resourceType'];
        }

        return $resourceType;
    }

    /**
     * Checks if the array is empty.
     *
     * @param array  $doiMetadataElementArray Metadata element array to check if empty and key exists.
     * @param string $keySearch               The key which needs to be checked if it exists in the array.
     */
    private function doesKeyExist(array $doiMetadataElementArray, string $keySearch): bool
    {
        if (!empty($doiMetadataElementArray)) {
            if (array_key_exists($keySearch, $doiMetadataElementArray[0])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks sync conditions for Dois.
     *
     * @param array $doiData            Dois metadata from Datacite.
     * @param array $emailRecipientList Email recipient list for DOI comparison report.
     */
    private function syncConditions(array $doiData, array $emailRecipientList): void
    {
        foreach ($doiData as $doi) {
            if ($doi['udi']) {
                $dataset = $this->getDataset($doi['udi']);
                if (!empty($dataset) and !$this->isOrphan($doi['doi'], $dataset)) {
                    $this->compareFields($doi, $dataset);
                } else {
                    // Error message
                    $this->outOfSyncDoi[$doi['doi']] = ['orphan' => 'Orphan/Duplicate'];
                }
            } else {
                $this->compareFields($doi);
            }
        }
        if (!empty($this->outOfSyncDoi)) {
            $this->mailer->sendEmailMessage(
                $this->twig->load('Email/data-repository-managers.out-of-sync-doi.email.twig'),
                ['dois' => $this->outOfSyncDoi],
                $emailRecipientList
            );
        }
    }

    /**
     * Gets a dataset by udi.
     *
     * @param string $udi Identifier used to get a dataset.
     */
    private function getDataset(string $udi): ?Dataset
    {
        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(['udi' => ['udi' => substr($udi, 0, 16)]]);

        if (!empty($datasets)) {
            $dataset = $datasets[0];
            if ($dataset instanceof Dataset) {
                return $dataset;
            }
        }

        return null;
    }

    /**
     * Compares fields of doi metadata from datactie and GRIIDC.
     *
     * @param array   $doiElements Doi metadata elements.
     * @param Dataset $dataset     A dataset instance.
     */
    private function compareFields(array $doiElements, Dataset $dataset = null): void
    {
        if ($dataset) {
            // Check title
            $this->doesStringExist(
                ['doi' => $doiElements['doi'], 'title' => $doiElements['title'], 'field' => 'title'],
                str_replace(',', '', $dataset->getTitle())
            );

            // Check author
            $creator = $dataset->getAuthors() ?: '(:tba)';

            $this->doesStringExist(
                ['doi' => $doiElements['doi'], 'author' => $doiElements['author'], 'field' => 'author'],
                str_replace(',', '', $creator),
                $dataset
            );

            // Check publisher
            $this->doesStringExist(
                ['doi' => $doiElements['doi'], 'publisher' => $doiElements['publisher'], 'field' => 'publisher'],
                'Harte Research Institute'
            );

            // CHeck resource type
            $this->doesStringExist(
                [
                    'doi' => $doiElements['doi'],
                    'resourceType' => $doiElements['resourceType'],
                    'field' => 'resourceType'
                ],
                'Dataset'
            );
            // Check doi state and url
            $this->isStateValid($dataset, $doiElements);
        } else {
            // Check title
            $this->doesStringExist(
                ['doi' => $doiElements['doi'], 'title' => $doiElements['title'], 'field' => 'title'],
                'inactive'
            );

            // Check author
            $this->doesStringExist(
                ['doi' => $doiElements['doi'], 'author' => $doiElements['author'], 'field' => 'author'],
                '(:null)'
            );

            // Check publisher
            $this->doesStringExist(
                ['doi' => $doiElements['doi'], 'publisher' => $doiElements['publisher'], 'field' => 'publisher'],
                'none supplied'
            );

            // CHeck resource type
            $this->doesStringExist(
                [
                    'doi' => $doiElements['doi'],
                    'resourceType' => $doiElements['resourceType'],
                    'field' => 'resourceType'
                ],
                'Dataset'
            );

            $doiStatus = $this->getDoiStatus($doiElements['state']);
            if ($doiStatus === DOI::STATE_REGISTERED) {
                if ($this->isUrlValid($doiElements['url'], 'invalidDOI') === false) {
                    // Error message
                    $this->outOfSyncDoi[$doiElements['doi']] = ['url' => 'Incorrect url', 'value' => $doiElements['url']];
                }
            } else {
                // Error message
                $this->outOfSyncDoi[$doiElements['doi']] = ['state' => 'Incorrect state'];
            }
        }
    }

    /**
     * Checks if the doi state is valid.
     *
     * @param Dataset $dataset     A dataset instance.
     * @param array   $doiElements Doi metadata elements.
     */
    private function isStateValid(Dataset $dataset, array $doiElements): void
    {
        $doiStatus = $this->getDoiStatus($doiElements['state']);

        if (
            $dataset->getDatasetStatus() === Dataset::DATASET_STATUS_NONE and
            $dataset->getIdentifiedStatus() === DIF::STATUS_APPROVED
        ) {
            if ($doiStatus === DOI::STATE_DRAFT || $doiStatus === DOI::STATE_REGISTERED) {
                if (!$this->isUrlValid($doiElements['url'], 'tombstone')) {
                    // Error message
                    $this->outOfSyncDoi[$doiElements['doi']] = ['url' => 'Incorrect url', 'value' => $doiElements['url']];
                }
            }
        } elseif ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_NONE) {
            if ($doiStatus === DOI::STATE_FINDABLE) {
                if ($dataset->isAvailable()) {
                    if (!$this->isUrlValid($doiElements['url'], 'data')) {
                        // Error message
                        $this->outOfSyncDoi[$doiElements['doi']] = ['url' => 'Incorrect url', 'value' => $doiElements['url']];
                    }
                } else {
                    if (!$this->isUrlValid($doiElements['url'], 'tombstone')) {
                        // Error message
                        $this->outOfSyncDoi[$doiElements['doi']] = ['url' => 'Incorrect url', 'value' => $doiElements['url']];
                    }
                }
            }
        } else {
            // Error message
            $this->outOfSyncDoi[$doiElements['doi']] = ['state' => 'Incorrect state'];
        }
    }

    /**
     * Gets the doi status according to griidc system.
     *
     * @param string $state Datacite doi state.
     */
    private function getDoiStatus(string $state): ?string
    {
        return match (true) {
            $state === self::DOI_DRAFT => DOI::STATE_DRAFT,
            $state === self::DOI_FINDABLE => DOI::STATE_FINDABLE,
            $state === self::DOI_REGISTERED => DOI::STATE_REGISTERED,
            default => null,
        };
    }

    /**
     * Check if the url is valid.
     *
     * @param string $url    The haystack string.
     * @param string $needle Needle to search the string.
     */
    private function isUrlValid(string $url, string $needle): bool
    {
        $url = str_replace('https://data.griidc.org/', '', $url);
        if (str_contains($url, $needle)) {
            return true;
        }

        return false;
    }

    /**
     * Compare strings case insensitive.
     *
     * @param array        $metadataElement   Doi metadata elements.
     * @param string       $comparisonElement String that needs to be compared.
     * @param Dataset|null $dataset           A dataset instance.
     */
    private function doesStringExist(array $metadataElement, string $comparisonElement, Dataset $dataset = null): void
    {
        if (empty($metadataElement[$metadataElement['field']])) {
            if (
                !empty($dataset)
                and $dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE
                and $metadataElement['field'] === 'author'
            ) {
                return;
            }
            //Error message
            $this->outOfSyncDoi[$metadataElement['doi']] = [$metadataElement['field'] => 'Null/Empty ' . $metadataElement['field']];
        } else {
            if (strcasecmp($comparisonElement, (string) $metadataElement[$metadataElement['field']]) !== 0) {
                if (!str_contains($comparisonElement, (string) $metadataElement[$metadataElement['field']])) {
                    //Error message
                    $this->outOfSyncDoi[$metadataElement['doi']] = [$metadataElement['field'] => 'Incorrect ' . $metadataElement['field'], 'value' => $metadataElement[$metadataElement['field']]];
                }
            }
        }
    }

    /**
     * Check if doi is orphan or duplicate.
     *
     * @param string  $doi     Doi identifier for the dataset.
     * @param Dataset $dataset A dataset instance.
     */
    private function isOrphan(string $doi, Dataset $dataset): bool
    {
        if (!$dataset->getDoi() instanceof DOI || strtolower($dataset->getDoi()->getDoi()) !== strtolower($doi)) {
            return true;
        }

        return false;
    }
}
