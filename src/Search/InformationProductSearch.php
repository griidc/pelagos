<?php

namespace App\Search;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\Term;
use FOS\ElasticaBundle\Finder\TransformedFinder;

/**
 * Util class for FOS Elastic Search.
 */
class InformationProductSearch
{
    /**
     * FOS Elastica Object to find elastica documents.
     *
     * @var TransformedFinder
     */
    protected $finder;

    /**
     * Constructor.
     *
     * @param TransformedFinder      $finder        The finder interface object.
     * @param EntityManagerInterface $entityManager An entity manager.
     */
    public function __construct(TransformedFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Find datasets using Fos Elastic search.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return SearchResults
     */
    public function search(SearchOptions $searchOptions): SearchResults
    {
        $queryString = $searchOptions->getQueryString();
        $isPublished = $searchOptions->shouldFilterOnlyPublishedInformationProducts();

        $simpleQuery = new SimpleQueryString($queryString);
        $simpleQuery->setDefaultOperator(SimpleQueryString::OPERATOR_AND);

        $query = new BoolQuery();
        $query->addMust($simpleQuery);

        $termQuery = new Term();
        $termQuery->setTerm('published', $isPublished);
        $query->addFilter($termQuery);

        $resultsPaginator = $this->finder->findPaginated($query);

        return new SearchResults($resultsPaginator, $searchOptions);
    }
}
