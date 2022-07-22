<?php

namespace App\Search;

use JMS\Serializer\Annotation as Serializer;
use Pagerfanta\PagerfantaInterface;

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
     * The results.
     *
     * @var iterable
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
    }
}
