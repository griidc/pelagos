<?php

namespace App\Search;

use App\Entity\Funder;
use App\Entity\ResearchGroup;
use App\Entity\DatasetSubmission;
use Pagerfanta\PagerfantaInterface;
use App\Repository\FunderRepository;
use App\Entity\ProductTypeDescriptor;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ResearchGroupRepository;
use JMS\Serializer\Annotation as Serializer;
use App\Entity\DigitalResourceTypeDescriptor;
use App\Repository\ProductTypeDescriptorRepository;
use App\Repository\DigitalResourceTypeDescriptorRepository;

/**
 * Search Results Class.
 */
class SearchResults
{
    /**
     * Pager Fanta Search Results.
     *
     * @var PagerfantaInterface
     *
     * @Serializer\Exclude
     */
    private $pagerFantaResults;

    /**
     * An instance of the SearchOptions.
     *
     * @var SearchOptions
     *
     * @Serializer\Exclude
     */
    private $searchOptions;

    /**
     * The number of results returned.
     *
     * @var int
     *
     * @Serializer\SerializedName("count")
     */
    private $numberOfResults;

    /**
     * Number of pages available.
     *
     * @var int
     *
     * @Serializer\SerializedName("pages")
     */
    private $numberOfPages;

    /**
     * Number of results per page.
     *
     * @var int
     *
     * @Serializer\SerializedName("resultPerPage")
     */
    private $resultsPerPage;

    /**
     * The current page of results.
     *
     * @var int
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
     *
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
     * Instance of the FunderRepository.
     *
     * @var FunderRepository
     *
     * @Serializer\Exclude
     */
    private $funderRepository;

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
     * @param PagerfantaInterface    $pagerFantaResults the Pager Fanta results
     * @param SearchOptions          $searchOptions     an instance of the SearchOptions
     * @param EntityManagerInterface $entityManager     an instance of the Entity Manager
     */
    public function __construct(PagerfantaInterface $pagerFantaResults, SearchOptions $searchOptions, EntityManagerInterface $entityManager)
    {
        $this->pagerFantaResults = $pagerFantaResults;
        $this->searchOptions = $searchOptions;
        $this->entityManager = $entityManager;

        $this->researchGroupRepository = $this->entityManager->getRepository(ResearchGroup::class);
        $this->digitalResourceTypeDescriptorRepository = $this->entityManager->getRepository(DigitalResourceTypeDescriptor::class);
        $this->productTypeDescriptorRepository = $this->entityManager->getRepository(ProductTypeDescriptor::class);
        $this->funderRepository = $this->entityManager->getRepository(Funder::class);

        $this->processResults();
    }

    /**
     * Processed the search results.
     */
    private function processResults(): void
    {
        $this->pagerFantaResults->setMaxPerPage($this->searchOptions->getMaxPerPage());

        $this->numberOfResults = $this->pagerFantaResults->getNbResults();
        $this->numberOfPages = $this->pagerFantaResults->getNbPages();
        $this->resultsPerPage = $this->pagerFantaResults->getMaxPerPage();
        $this->currentPage = ($this->searchOptions->getCurrentPage() <= $this->numberOfPages) ? $this->searchOptions->getCurrentPage() : 1;
        $this->pagerFantaResults->setCurrentPage($this->currentPage);

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

        // Funder aggregation
        $funderAggregations = $this->findKey($aggregations, 'funders_aggregation');
        if (array_key_exists('buckets', $funderAggregations)) {
            $funderBucket = array_column(
                $funderAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['fundersInfo'] = $this->funderRepository->getFunderInfo($funderBucket);
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

        $this->facetInfo['researchGroupInfo'] = $this->researchGroupRepository->getResearchGroupsInfo($researchGroupBucket);

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
    }

    /**
     * Get dataset availability status information for the aggregations.
     *
     * @param array $aggregations aggregations for each availability status
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
                'count' => $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE),
            ],
            [
                'id' => 2,
                'name' => 'Submitted',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL)
                ),
            ],
            [
                'id' => 3,
                'name' => 'Restricted',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED)
                ),
            ],
            [
                'id' => 4,
                'name' => 'Available',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE)
                ),
            ],
        ];

        // Remove any element with a count of 0.
        foreach ($statusInfo as $key => $value) {
            if (0 === $value['count']) {
                unset($statusInfo[$key]);
            }
        }

        // Sorting based on highest count
        $array_column = array_column($statusInfo, 'count');
        array_multisort($array_column, SORT_DESC, $statusInfo);

        return $statusInfo;
    }

    /**
     * Get the facet info to Data Type.
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

        $combinedBuckets = [];
        foreach (array_keys($datasetBucket + $infoProductBucket) as $key) {
            $combinedBuckets[$key] = (isset($datasetBucket[$key]) ? $datasetBucket[$key] : 0) + (isset($infoProductBucket[$key]) ? $infoProductBucket[$key] : 0);
        }

        return $combinedBuckets;
    }

    /**
     * Find the bucket name of the aggregation.
     *
     * @param array  $aggregations array of aggregations
     * @param string $bucketKey    the name of the bucket to be found
     */
    private function findKey(array $aggregations, string $bucketKey): array
    {
        $bucket = [];

        // create a recursive iterator to loop over the array recursively
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($aggregations),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        // loop over the iterator
        foreach ($iterator as $key => $value) {
            // if the key matches our search
            if ($key === $bucketKey) {
                // add the current key
                $keys = [$key];
                // loop up the recursive chain
                for ($i = ($iterator->getDepth() - 1); $i >= 0; --$i) {
                    // add each parent key
                    array_unshift($keys, $iterator->getSubIterator($i)->key());
                }
                // return our output array
                $bucket = $value;
            }
        }

        return $bucket;
    }
}
