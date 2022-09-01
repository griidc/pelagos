<?php

namespace App\Search;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\ProductTypeDescriptor;
use App\Repository\DigitalResourceTypeDescriptorRepository;
use App\Repository\ProductTypeDescriptorRepository;
use App\Repository\ResearchGroupRepository;
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
     * Instance of the ResearchGroupRepository.
     *
     * @var DigitalResourceTypeDescriptorRepository
     *
     * @Serializer\Exclude
     */
    private $digitalResourceTypeDescriptorRepository;

    /**
     * Instance of the ResearchGroupRepository.
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

        $this->processResults();
    }

    /**
     * Processed the search results.
     *
     * @return void
     */
    private function processResults(): void
    {
        $this->pagerFantaResults->setCurrentPage($this->searchOptions->getCurrentPage());
        $this->pagerFantaResults->setMaxPerPage($this->searchOptions->getMaxPerPage());

        $this->numberOfResults = $this->pagerFantaResults->getNbResults();
        $this->numberOfPages = $this->pagerFantaResults->getNbPages();
        $this->resultsPerPage = $this->pagerFantaResults->getMaxPerPage();

        $this->result = $this->pagerFantaResults->getCurrentPageResults();

        $aggregations = $this->pagerFantaResults->getAdapter()->getAggregations();

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

        $researchGroupBucket = $this->getResearchGroupBucket($aggregations);

        $this->facetInfo['researchGroupInfo'] = $this->researchGroupRepository->getResearchGroupsInfo($researchGroupBucket);
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
    private function getResearchGroupBucket($aggregations): array
    {
        $datasetResearchGroupBucket = [];
        $datasetResearchGroupAggregations = $this->findKey($aggregations, 'research_group_aggregation');
        if (array_key_exists('buckets', $datasetResearchGroupAggregations)) {
            $datasetResearchGroupBucket = array_column(
                $datasetResearchGroupAggregations['buckets'],
                'doc_count',
                'key'
            );
        }

        $infoProductsResearchGroupBucket = [];
        $infoProductsResearchGroupAggregations = $this->findKey($aggregations, 'research_groups_aggregation');
        if (array_key_exists('buckets', $infoProductsResearchGroupAggregations)) {
            $infoProductsResearchGroupBucket = array_column(
                $infoProductsResearchGroupAggregations['buckets'],
                'doc_count',
                'key'
            );
        }

        $researchGroupBucketKeys = array_merge(array_keys($datasetResearchGroupBucket), array_keys($infoProductsResearchGroupBucket));
        $researchGroupBucketValues = array_merge(array_values($datasetResearchGroupBucket), array_values($infoProductsResearchGroupBucket));

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
