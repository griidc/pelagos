<?php

namespace App\Util;

use App\Entity\InformationProduct;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\Term;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

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
     */
    public function findInformationProduct(string $queryString = '*', bool $isPublished = true)
    {
        $queryString = empty($queryString) ? '*' : $queryString;

        $simpleQuery = new SimpleQueryString($queryString);
        $simpleQuery->setDefaultOperator(SimpleQueryString::OPERATOR_AND);

        $query = new BoolQuery();
        $query->addMust($simpleQuery);

        $termQuery = new Term();
        $termQuery->setTerm('published', $isPublished);
        $query->addFilter($termQuery);

        return $this->finder->findPaginated($query);
    }
}
