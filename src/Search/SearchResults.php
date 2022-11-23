<?php

namespace App\Search;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\DatasetSubmission;
use App\Entity\FundingOrganization;
use App\Entity\ProductTypeDescriptor;
use App\Repository\DigitalResourceTypeDescriptorRepository;
use App\Repository\ProductTypeDescriptorRepository;
use App\Repository\ResearchGroupRepository;
use App\Repository\FundingOrganizationRepository;
use App\Entity\ResearchGroup;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Annotation as Serializer;
use Pagerfanta\PagerfantaInterface;

/**
 * Search Results Class.
 */
class SearchResults
{
    /**
     * Pager Fanta Search Results.
     *
     * @var PagerfantaInterface $pagerFantaResults
     *
     * @Serializer\Exclude
     */
    private $pagerFantaResults;

    /**
     * An instance of the SearchOptions.
     *
     * @var SearchOptions $searchOptions
     *
     * @Serializer\Exclude
     */
    private $searchOptions;

    /**
     * The number of results returned.
     *
     * @var integer
     *
     * @Serializer\SerializedName("count")
     */
    private $numberOfResults;

    /**
     * Number of pages available.
     *
     * @var integer
     *
     * @Serializer\SerializedName("pages")
     */
    private $numberOfPages;

    /**
     * Number of results per page.
     *
     * @var integer
     *
     * @Serializer\SerializedName("resultPerPage")
     */
    private $resultsPerPage;

    /**
     * The current page of results.
     *
     * @var integer
     *
     * @Serializer\SerializedName("currentPage")
     */
    private $currentPage;

    /**
     * Facet Information for aggregations.
     *
     * @var array
     */
    private $facetInfo;

    /**
     * The results.
     *
     * @var object|iterable
     *
     * @Serializer\SerializedName("results")
     * @Serializer\Groups({"search"})
     */
    private $result;

    /**
     * Instance of the EntityManager.
     *
     * @var EntityManagerInterface
     *
     * @Serializer\Exclude
     */
    private $entityManager;

    /**
     * Instance of the ResearchGroupRepository.
     *
     * @var ResearchGroupRepository
     *
     * @Serializer\Exclude
     */
    private $researchGroupRepository;

    /**
     * Instance of the FundingOrganizationRepository.
     *
     * @var FundingOrganizationRepository
     *
     * @Serializer\Exclude
     */
    private $fundingOrganizationRepository;

    /**
     * Instance of the DigitalResourceTypeDescriptorRepository.
     *
     * @var DigitalResourceTypeDescriptorRepository
     *
     * @Serializer\Exclude
     */
    private $digitalResourceTypeDescriptorRepository;

    /**
     * Instance of the ProductTypeDescriptorRepository.
     *
     * @var ProductTypeDescriptorRepository
     *
     * @Serializer\Exclude
     */
    private $productTypeDescriptorRepository;

    /**
     * Class Contructor.
     *
     * @param PagerfantaInterface    $pagerFantaResults The Pager Fanta results.
     * @param SearchOptions          $searchOptions     An instance of the SearchOptions.
     * @param EntityManagerInterface $entityManager     An instance of the Entity Manager.
     */
    public function __construct(PagerfantaInterface $pagerFantaResults, SearchOptions $searchOptions, EntityManagerInterface $entityManager)
    {
        $this->pagerFantaResults = $pagerFantaResults;
        $this->searchOptions = $searchOptions;
        $this->entityManager = $entityManager;

        $this->researchGroupRepository = $this->entityManager->getRepository(ResearchGroup::class);
        $this->digitalResourceTypeDescriptorRepository = $this->entityManager->getRepository(DigitalResourceTypeDescriptor::class);
        $this->productTypeDescriptorRepository = $this->entityManager->getRepository(ProductTypeDescriptor::class);
        $this->fundingOrganizationRepository = $this->entityManager->getRepository(FundingOrganization::class);

        $this->processResults();
    }

    /**
     * Processed the search results.
     *
     * @return void
     */
    private function processResults(): void
    {
        $this->currentPage = $this->searchOptions->getCurrentPage();
        $this->pagerFantaResults->setCurrentPage($this->currentPage);
        $this->pagerFantaResults->setMaxPerPage($this->searchOptions->getMaxPerPage());

        $this->numberOfResults = $this->pagerFantaResults->getNbResults();
        $this->numberOfPages = $this->pagerFantaResults->getNbPages();
        $this->resultsPerPage = $this->pagerFantaResults->getMaxPerPage();

        $this->result = $this->pagerFantaResults->getCurrentPageResults();

        $aggregations = $this->pagerFantaResults->getAdapter()->getAggregations();

        // Data type aggregation
        $dataTypeAggregations = $this->findKey($aggregations, 'friendly_name_agregation');
        if (array_key_exists('buckets', $dataTypeAggregations)) {
            $dataTypeBucket = array_column(
                $dataTypeAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['dataTypeInfo'] = $this->bucketToInfoArray($dataTypeBucket);
        }

        // Status info aggregation
        $datasetStatusAggregations = $this->findKey($aggregations, 'status');
        if (array_key_exists('buckets', $datasetStatusAggregations)) {
            $datasetStatusBucket = array_column(
                $datasetStatusAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['statusInfo'] = $this->getStatusInfo($datasetStatusBucket);
        }

        // Tags info aggregation
        $tagsAggregations = $this->findKey($aggregations, 'tags_agg');
        if (array_key_exists('buckets', $tagsAggregations)) {
            $tagsBucket = array_column(
                $tagsAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['tagsInfo'] = $this->bucketToInfoArray($tagsBucket);
        }

        // Product type aggregation
        $productTypeDescriptorAggregations = $this->findKey($aggregations, 'product_type_aggregation');
        if (array_key_exists('buckets', $productTypeDescriptorAggregations)) {
            $productTypeDescriptorBucket = array_column(
                $productTypeDescriptorAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['productTypeDescriptorInfo'] = $this->productTypeDescriptorRepository->getProductTypeDescriptorInfo($productTypeDescriptorBucket);
        }

        // Digital resource type aggregation
        $digitalResourceTypeDescriptorAggregations = $this->findKey($aggregations, 'digital_resource_aggregation');
        if (array_key_exists('buckets', $digitalResourceTypeDescriptorAggregations)) {
            $digitalResourceTypeDescriptorBucket = array_column(
                $digitalResourceTypeDescriptorAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['digitalResourceTypeDescriptorsInfo'] = $this->digitalResourceTypeDescriptorRepository->getDigitalResourceTypeDescriptorsInfo($digitalResourceTypeDescriptorBucket);
        }

        $researchGroupBucket = $this->combineBuckets($aggregations, 'research_group_aggregation', 'research_groups_aggregation');
        $fundingOrgBucket = $this->combineBuckets($aggregations, 'funding_organization_aggregation', 'funding_organizations_aggregation');

        $this->facetInfo['researchGroupInfo'] = $this->researchGroupRepository->getResearchGroupsInfo($researchGroupBucket);
        $this->facetInfo['fundingOrgInfo'] = $this->fundingOrganizationRepository->getFundingOrgInfo($fundingOrgBucket);
    }

    /**
     * Get dataset availability status information for the aggregations.
     *
     * @param array $aggregations Aggregations for each availability status.
     *
     * @return array
     */
    private function getStatusInfo(array $aggregations): array
    {
        $datasetCount = function ($status) use ($aggregations) {
            if (array_key_exists($status, $aggregations)) {
                return $aggregations[$status];
            } else {
                return 0;
            }
        };

        $statusInfo = [
            [
                'id' => 1,
                'name' => 'Identified',
                'count' => $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE)
            ],
            [
                'id' => 2,
                'name' => 'Submitted',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL)
                )
            ],
            [
                'id' => 3,
                'name' => 'Restricted',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED)
                )
            ],
            [
                'id' => 4,
                'name' => 'Available',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE)
                )
            ],
        ];

        // Remove any element with a count of 0.
        foreach ($statusInfo as $key => $value) {
            if (0 === $value['count']) {
                unset($statusInfo[$key]);
            }
        }

        //Sorting based on highest count
        $array_column = array_column($statusInfo, 'count');
        array_multisort($array_column, SORT_DESC, $statusInfo);
        return $statusInfo;
    }

    /**
     * Get the facet info to Data Type.
     *
     * @param array $bucket
     *
     * @return array
     */
    private function bucketToInfoArray(array $bucket): array
    {
        $infoArray = [];
        foreach ($bucket as $type => $count) {
            $info = [];
            $info['id'] = $type;
            $info['name'] = $type;
            $info['count'] = $count;
            $infoArray[] = $info;
        }
        return $infoArray;
    }

    /**
     * Get combined research group aggregation values.
     *
     * @param $aggregations
     *
     * @return array
     */
    private function combineBuckets($aggregations, string $datasetBucketName, string $infoProductBucketName): array
    {
        $datasetBucket = [];
        $datasetAggregations = $this->findKey($aggregations, $datasetBucketName);
        if (array_key_exists('buckets', $datasetAggregations)) {
            $datasetBucket = array_column(
                $datasetAggregations['buckets'],
                'doc_count',
                'key'
            );
        }

        $infoProductBucket = [];
        $infoProductAggregations = $this->findKey($aggregations, $infoProductBucketName);
        if (array_key_exists('buckets', $infoProductAggregations)) {
            $infoProductBucket = array_column(
                $infoProductAggregations['buckets'],
                'doc_count',
                'key'
            );
        }

        $combinedBuckets = array();
        foreach (array_keys($datasetBucket + $infoProductBucket) as $key) {
            $combinedBuckets[$key] = (isset($datasetBucket[$key]) ? $datasetBucket[$key] : 0) + (isset($infoProductBucket[$key]) ? $infoProductBucket[$key] : 0);
        }

        return $combinedBuckets;
    }

    /**
     * Find the bucket name of the aggregation.
     *
     * @param array  $aggregations Array of aggregations.
     * @param string $bucketKey    The name of the bucket to be found.
     *
     * @return array
     */
    private function findKey(array $aggregations, string $bucketKey): array
    {
        $bucket = array();

        //create a recursive iterator to loop over the array recursively
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($aggregations),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        //loop over the iterator
        foreach ($iterator as $key => $value) {
            //if the key matches our search
            if ($key === $bucketKey) {
                //add the current key
                $keys = array($key);
                //loop up the recursive chain
                for ($i = ($iterator->getDepth() - 1); $i >= 0; $i--) {
                    //add each parent key
                    array_unshift($keys, $iterator->getSubIterator($i)->key());
                }
                //return our output array
                $bucket = $value;
            }
        }
        return $bucket;
    }
}
