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
     * @Serializer\SerializedName("result")
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
     * @Serializer\SerializedName("informationProducts")
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

        $researchGroupBucket = array_column(
            $this->findKey($aggregations, 'research_group_aggregation')['buckets'],
            'doc_count',
            'key'
        );

        $productTypeDescriptorBucket = array_column(
            $this->findKey($aggregations, 'product_type_aggregation')['buckets'],
            'doc_count',
            'key'
        );

        $digitalResourceTypeDescriptorBucket = array_column(
            $this->findKey($aggregations, 'digital_resource_aggregation')['buckets'],
            'doc_count',
            'key'
        );

        $this->facetInfo['researchGroupInfo'] = $this->researchGroupRepository->getResearchGroupsInfo($researchGroupBucket);
        $this->facetInfo['digitalResourceTypeDescriptorsInfo'] = $this->digitalResourceTypeDescriptorRepository->getDigitalResourceTypeDescriptorsInfo($digitalResourceTypeDescriptorBucket);
        $this->facetInfo['productTypeDescriptorInfo'] = $this->productTypeDescriptorRepository->getProductTypeDescriptorInfo($productTypeDescriptorBucket);
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
