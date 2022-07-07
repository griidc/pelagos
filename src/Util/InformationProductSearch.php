<?php

namespace App\Util;

use App\Entity\InformationProduct;
use Elastica\Query;
use Elastica\Query\SimpleQueryString;
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
    public function findInformationProduct(string $queryString)
    {
        $query = new SimpleQueryString($queryString);

        return $this->finder->findPaginated($queryString);
    }

}
