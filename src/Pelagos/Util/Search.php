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
    public function findDatasets(string $queryTerm, int $page, array $options = []): array
    {
        $query = $this->buildQuery($queryTerm, $page, $options);

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
    public function getCount(string $queryTerm, array $options = []): int
    {
        $userPaginator = $this->getPagiantor($queryTerm, $options);

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
    private function buildQuery(string $queryTerm, int $page = 1, array $options = []): \Elastica\Query
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

        $agg = new \Elastica\Aggregation\Terms('researchGrpId');
        $nestedAgg = new \Elastica\Aggregation\Nested('nested', 'researchGroup');
        $agg->setField('researchGroup.id');
        $agg->setSize(500);
        $nestedAgg->addAggregation($agg);
        $mainQuery->addAggregation($nestedAgg);
        if (isset($options['rgId'])) {
            $researchGroupNameQuery = new \Elastica\Query\Nested();
            $researchGroupNameQuery->setPath('researchGroup');
            $rgNamequery = new \Elastica\Query\Terms();
            $rgNamequery->setTerms('researchGroup.id', [$options['rgId']]);
            $researchGroupNameQuery->setQuery($rgNamequery);
            $boolQuery->addFilter($researchGroupNameQuery);
        }

        $mainQuery->setQuery($boolQuery);
        $mainQuery->setFrom(($page - 1) * 10);

        return $mainQuery;
    }

    /**
     * Get the paginator adapter for the query.
     *
     * @param string $queryTerm Query string.
     *
     * @return \Pagerfanta\Pagerfanta
     */
    private function getPagiantor(string $queryTerm, array $options = []): \Pagerfanta\Pagerfanta
    {
        $query = $this->buildQuery($queryTerm, 1, $options);

        $userPaginator = $this->finder->findPaginated($query);

        return $userPaginator;
    }

    /**
     * Get the aggregations for the query.
     *
     * @param string $queryTerm Query string.
     *
     * @return array
     */
    public function getAggregations(string $queryTerm, array $options = []): array
    {
        $userPaginator = $this->getPagiantor($queryTerm, $options);

        $aggs = array_column($userPaginator->getAdapter()->getAggregations()['nested']['researchGrpId']['buckets'], 'doc_count', 'key');

        return $aggs;
    }
}
