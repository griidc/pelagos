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
class DoiComparisonCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:dataset-doi:comparison';

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
    protected $outOfSyncDoi = array();

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
        $this->setDescription('DOI comparison tool.')
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emailRecipientList = $input->getArgument('emailRecipientList');
        $response = null;
        $doiJson = array();
        $doiData = array();
        $pageNumber = 1;

        do {
            $body = $this->doiUtil->getDoiCollection($pageNumber);
            $doiJson[$pageNumber] = $body['data'];
            $pageNumber++;
        } while (array_key_exists('next', $body['links']));

        foreach ($doiJson as $dois) {
            foreach ($dois as $doi) {
                $doiData[$doi['id']] = array(
                    'doi' => $doi['attributes']['doi'],
                    'url' => $doi['attributes']['url'],
                    'udi' => $this->getUdi($doi['attributes']['url']),
                    'title' => str_replace(
                        ',',
                        '',
                        $this->doesKeyExist($doi['attributes']['titles'], 'title')
                            ? $doi['attributes']['titles'][0]['title'] : ''
                    ),
                    'author' => str_replace(
                        ',',
                        '',
                        $this->doesKeyExist($doi['attributes']['creators'], 'name')
                            ? $doi['attributes']['creators'][0]['name'] : ''
                    ),
                    'publisher' => $doi['attributes']['publisher'],
                    'state' => $doi['attributes']['state'],
                    'resourceType' => $this->getResourceType($doi['attributes']['types'])
                );
            }
        }

        $this->syncConditions($doiData, $emailRecipientList);
        return 0;
    }

    /**
     * Get udi from Url.
     *
     * @param string $url Url that needs to be fetched.
     *
     * @return null
     */
    private function getUdi(string $url)
    {
        $udi = null;
        $udiRegEx = '/\b([A-Z\d]{2}\.x\d\d\d\.\d\d\d:\d\d\d\d)\b/';
        if (preg_match_all($udiRegEx, $url, $matches)) {
            trim(preg_replace($udiRegEx, '', $url));
            $udi = $matches[1][0];
        }

        return $udi;
    }

    /**
     * Get the resource type for the Doi.
     *
     * @param array $types Types of resources from doi.
     *
     * @return string
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
     *
     * @return boolean
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
     *
     * @return void
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
                    $this->outOfSyncDoi[$doi['doi']] = array(
                        'orphan' => 'Orphan/Duplicate'
                    );
                }
            } else {
                $this->compareFields($doi);
            }
        }
        if (!empty($this->outOfSyncDoi)) {
            $this->mailer->sendEmailMessage(
                $this->twig->load('Email/data-repository-managers.out-of-sync-doi.email.twig'),
                array('dois' => $this->outOfSyncDoi),
                $emailRecipientList
            );
        }
    }

    /**
     * Gets a dataset by udi.
     *
     * @param string $udi Identifier used to get a dataset.
     *
     * @return Dataset|null
     */
    private function getDataset(string $udi): ?Dataset
    {
        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(array(
            'udi' => array('udi' => substr($udi, 0, 16))
        ));

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
     *
     * @return void
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
            $creator = ($dataset->getAuthors()) ? $dataset->getAuthors() : '(:tba)';

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
                    $this->outOfSyncDoi[$doiElements['doi']] = array(
                        'url' => 'Incorrect url',
                        'value' => $doiElements['url']
                    );
                }
            } else {
                // Error message
                $this->outOfSyncDoi[$doiElements['doi']] = array('state' => 'Incorrect state');
            }
        }
    }

    /**
     * Checks if the doi state is valid.
     *
     * @param Dataset $dataset     A dataset instance.
     * @param array   $doiElements Doi metadata elements.
     *
     * @return void
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
                    $this->outOfSyncDoi[$doiElements['doi']] = array(
                        'url' => 'Incorrect url',
                        'value' => $doiElements['url']
                    );
                }
            }
        } elseif ($dataset->getDatasetStatus() !== Dataset::DATASET_STATUS_NONE) {
            if ($doiStatus === DOI::STATE_FINDABLE) {
                if ($dataset->isAvailable()) {
                    if (!$this->isUrlValid($doiElements['url'], 'data')) {
                        // Error message
                        $this->outOfSyncDoi[$doiElements['doi']] = array(
                            'url' => 'Incorrect url',
                            'value' => $doiElements['url']
                            );
                    }
                } else {
                    if (!$this->isUrlValid($doiElements['url'], 'tombstone')) {
                        // Error message
                        $this->outOfSyncDoi[$doiElements['doi']] = array(
                            'url' => 'Incorrect url',
                            'value' => $doiElements['url']
                            );
                    }
                }
            }
        } else {
            // Error message
            $this->outOfSyncDoi[$doiElements['doi']] = array('state' => 'Incorrect state');
        }
    }

    /**
     * Gets the doi status according to griidc system.
     *
     * @param string $state Datacite doi state.
     *
     * @return string
     */
    private function getDoiStatus(string $state): string
    {
        switch (true) {
            case ($state === self::DOI_DRAFT):
                return DOI::STATE_DRAFT;
                break;
            case ($state === self::DOI_FINDABLE):
                return DOI::STATE_FINDABLE;
                break;
            case ($state === self::DOI_REGISTERED):
                return DOI::STATE_REGISTERED;
                break;
        }
    }

    /**
     * Check if the url is valid.
     *
     * @param string $url    The haystack string.
     * @param string $needle Needle to search the string.
     *
     * @return boolean
     */
    private function isUrlValid(string $url, string $needle): bool
    {
        $url = str_replace('https://data.gulfresearchinitiative.org/', '', $url);
        if (strpos($url, $needle) !== false) {
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
     *
     * @return void
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
            $this->outOfSyncDoi[$metadataElement['doi']] = array(
                $metadataElement['field'] => 'Null/Empty ' . $metadataElement['field']
            );
        } else {
            if (strcasecmp($comparisonElement, $metadataElement[$metadataElement['field']]) !== 0) {
                if (strpos($comparisonElement, $metadataElement[$metadataElement['field']]) === false) {
                    //Error message
                    $this->outOfSyncDoi[$metadataElement['doi']] = array(
                        $metadataElement['field'] => 'Incorrect ' . $metadataElement['field'],
                        'value' => $metadataElement[$metadataElement['field']]
                    );
                }
            }
        }
    }

    /**
     * Check if doi is orphan or duplicate.
     *
     * @param string  $doi     Doi identifier for the dataset.
     * @param Dataset $dataset A dataset instance.
     *
     * @return boolean
     */
    private function isOrphan(string $doi, Dataset $dataset): bool
    {
        if (!$dataset->getDoi() instanceof DOI || strtolower($dataset->getDoi()->getDoi()) !== strtolower($doi)) {
            return true;
        }

        return false;
    }
}
