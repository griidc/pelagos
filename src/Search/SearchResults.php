<?php

namespace App\Search;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\InformationProduct;
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
     * TODO: remove this probably.
     *
     * @Serializer\Exclude
     *
     * @var [type]
     */
    private $aggregations;

    private $researchGroupBucket;

    private $productTypeDescriptorBucket;

    private $digitalResourceTypeDescriptorBucket;

    /**
     * The results.
     *
     * @var object|iterable
     *
     * @Serializer\SerializedName("informationProducts")
     */
    private $result;

    /**
     * Class Contructor.
     *
     * @param PagerfantaInterface $pagerFantaResults The Pager Fanta results.
     * @param SearchOptions       $searchOptions     An instance of the SearchOptions.
     */
    public function __construct(PagerfantaInterface $pagerFantaResults, SearchOptions $searchOptions)
    {
        $this->pagerFantaResults = $pagerFantaResults;
        $this->searchOptions = $searchOptions;

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

        $this->aggregations = $this->pagerFantaResults->getAdapter()->getAggregations();

        $this->researchGroupBucket = array_column(
            $this->findKey($this->aggregations, 'research_group_aggregation')['buckets'],
            'doc_count',
            'key'
        );

        $this->productTypeDescriptorBucket = array_column(
            $this->findKey($this->aggregations, 'product_type_aggregation')['buckets'],
            'doc_count',
            'key'
        );

        $this->digitalResourceTypeDescriptorBucket = array_column(
            $this->findKey($this->aggregations, 'digital_resource_aggregation')['buckets'],
            'doc_count',
            'key'
        );
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
