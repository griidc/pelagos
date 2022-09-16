<?php

namespace App\Search;

use App\Entity\DigitalResourceTypeDescriptor;
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
     * @var Pagerfanta $pagerFantaResults
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

        $dataTypeAggregations = $this->findKey($aggregations, 'friendly_name_agregation');
        if (array_key_exists('buckets', $dataTypeAggregations)) {
            $dataTypeBucket = array_column(
                $dataTypeAggregations['buckets'],
                'doc_count',
                'key'
            );
            // dd($dataTypeBucket);
            $this->facetInfo['dataTypeInfo'] = $this->getDatasetTypeInfo($dataTypeBucket);
        }

        $productTypeDescriptorAggregations = $this->findKey($aggregations, 'product_type_aggregation');
        if (array_key_exists('buckets', $productTypeDescriptorAggregations)) {
            $productTypeDescriptorBucket = array_column(
                $productTypeDescriptorAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['productTypeDescriptorInfo'] = $this->productTypeDescriptorRepository->getProductTypeDescriptorInfo($productTypeDescriptorBucket);
        }

        $digitalResourceTypeDescriptorAggregations = $this->findKey($aggregations, 'digital_resource_aggregation');
        if (array_key_exists('buckets', $digitalResourceTypeDescriptorAggregations)) {
            $digitalResourceTypeDescriptorBucket = array_column(
                $digitalResourceTypeDescriptorAggregations['buckets'],
                'doc_count',
                'key'
            );
            $this->facetInfo['digitalResourceTypeDescriptorsInfo'] = $this->digitalResourceTypeDescriptorRepository->getDigitalResourceTypeDescriptorsInfo($digitalResourceTypeDescriptorBucket);
        }

        // $researchGroupBucket = $this->getResearchGroupBucket($aggregations);
        $researchGroupBucket = $this->combineBuckets($aggregations, 'research_group_aggregation', 'research_groups_aggregation');
        $fundingOrgBucket = $this->combineBuckets($aggregations, 'funding_organization_aggregation', 'funding_organizations_aggregation');

        $this->facetInfo['researchGroupInfo'] = $this->researchGroupRepository->getResearchGroupsInfo($researchGroupBucket);
        $this->facetInfo['fundingOrgInfo'] = $this->fundingOrganizationRepository->getFundingOrgInfo($fundingOrgBucket);

    }

    private function getDatasetTypeInfo($dataTypeBucket): array
    {
        $dataTypeInfo = [];
        foreach ($dataTypeBucket as $type => $count) {
            $typeInfo['id'] = $type;
            $typeInfo['name'] = $type;
            $typeInfo['count'] = $count;
            $dataTypeInfo[] = $typeInfo;
        }
        return $dataTypeInfo;
    }

    private function getFacetInfo(string $facet, array $facetAgregation): ?array
    {
        switch ($facet) {
            case 'researchGroup':
                return $this->researchGroupRepository->getResearchGroupsInfo($facetAgregation);
                break;
            case 'digitalResourceTypeDescriptors':
                return $this->digitalResourceTypeDescriptorRepository->getDigitalResourceTypeDescriptorsInfo($facetAgregation);
                break;
            case 'productTypeDescriptors':
                return $this->productTypeDescriptorRepository->getProductTypeDescriptorInfo($facetAgregation);
                break;
            default:
                return null;
        }
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

        $bucketKeys = array_merge(array_keys($datasetBucket), array_keys($infoProductBucket));
        $bucketValues = array_merge(array_values($datasetBucket), array_values($infoProductBucket));

        return array_combine($bucketKeys, $bucketValues);
    }

    /**
     * Get combined research group aggregation values.
     *
     * @param $aggregations
     *
     * @return array
     */
    private function getResearchGroupBucket($aggregations): array
    {
        $datasetBucket = [];
        $datasetAggregations = $this->findKey($aggregations, 'research_group_aggregation');
        if (array_key_exists('buckets', $datasetAggregations)) {
            $datasetBucket = array_column(
                $datasetAggregations['buckets'],
                'doc_count',
                'key'
            );
        }

        $infoProductBucket = [];
        $infoProductAggregations = $this->findKey($aggregations, 'research_groups_aggregation');
        if (array_key_exists('buckets', $infoProductAggregations)) {
            $infoProductBucket = array_column(
                $infoProductAggregations['buckets'],
                'doc_count',
                'key'
            );
        }

        $researchGroupBucketKeys = array_merge(array_keys($datasetBucket), array_keys($infoProductBucket));
        $researchGroupBucketValues = array_merge(array_values($datasetBucket), array_values($infoProductBucket));

        return array_combine($researchGroupBucketKeys, $researchGroupBucketValues);
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
