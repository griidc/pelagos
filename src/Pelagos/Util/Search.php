<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;
use FOS\ElasticaBundle\Finder\FinderInterface;

/**
 * Util class for FOS Elastic Search.
 */
class Search
{
    /**
     * The entity manager to use.
     *
     * @var EntityManager
     */
    protected $finder;

    /**
     * Constructor.
     *
     * @param FinderInterface $finder The finder interface object.
     */
    public function __construct(FinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Find datasets using Fos Elastic search.
     *
     * @param string  $queryTerm Query string.
     * @param integer $page      Page number of the search page.
     *
     * @return array
     */
    public function findDatasets(string $queryTerm, int $page): array
    {
        $query = $this->buildQuery($queryTerm, $page);

        $results = $this->finder->find($query);

        return $results;
    }

    /**
     * Get number of results.
     *
     * @param string $queryTerm Query string.
     *
     * @return integer
     */
    public function countDatasets(string $queryTerm): int
    {
        $query = $this->buildQuery($queryTerm);

        $userPaginator = $this->finder->findPaginated($query);
        $countResults = $userPaginator->getNbResults();

        return $countResults;
    }

    /**
     * Build Query using Fos Elastic search.
     *
     * @param string  $queryTerm Query string.
     * @param integer $page      Page start value for the search query.
     *
     * @return \Elastica\Query
     */
    private function buildQuery(string $queryTerm, int $page = 0): \Elastica\Query
    {
        $mainQuery = new \Elastica\Query();
        $boolQuery = new \Elastica\Query\BoolQuery();

        $titleQuery = new \Elastica\Query\Match();
        $titleQuery->setFieldQuery('title', $queryTerm);
        $titleQuery->setFieldOperator('title', 'and');
        $boolQuery->addShould($titleQuery);

        $datasetSubmissionQuery = new \Elastica\Query\Nested();
        $datasetSubmissionQuery->setPath('datasetSubmission');
        $authorQuery = new \Elastica\Query\Match();
        $authorQuery->setFieldQuery('datasetSubmission.authors', $queryTerm);
        $datasetSubmissionQuery->setQuery($authorQuery);

        $boolQuery->addShould($datasetSubmissionQuery);
        $mainQuery->setQuery($boolQuery);
        $mainQuery->setFrom(($page - 1) * 10);

        return $mainQuery;
    }
}
