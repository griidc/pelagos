<?php

namespace Pelagos\Util;

use Elastica\Aggregation;
use Elastica\Query;

use FOS\ElasticaBundle\Finder\TransformedFinder;

use Pagerfanta\Pagerfanta;

/**
 * Util class for FOS Elastic Search.
 */
class Search
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
     * @param TransformedFinder $finder The finder interface object.
     */
    public function __construct(TransformedFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Find datasets using Fos Elastic search.
     *
     * @param string  $queryTerm Query string.
     * @param integer $page      Page number of the search page.
     * @param array   $options   Options for the query.
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
     * @param array  $options   Options for the query.
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
     * @param array   $options   Options for the query.
     *
     * @return Query
     */
    private function buildQuery(string $queryTerm, int $page = 1, array $options = []): Query
    {
        $mainQuery = new Query();
        $subMainQuery = new Query\BoolQuery();
        $filterBoolQuery = new Query\BoolQuery();
        $fieldsBoolQuery = new Query\BoolQuery();

        $titleQuery = new Query\Match();
        $titleQuery->setFieldQuery('title', $queryTerm);
        $titleQuery->setFieldOperator('title', 'and');
        $fieldsBoolQuery->addShould($titleQuery);

        $datasetSubmissionQuery = new Query\Nested();
        $datasetSubmissionQuery->setPath('datasetSubmission');
        $authorQuery = new Query\Match();
        $authorQuery->setFieldQuery('datasetSubmission.authors', $queryTerm);
        $datasetSubmissionQuery->setQuery($authorQuery);

        $fieldsBoolQuery->addShould($datasetSubmissionQuery);

        $agg = new Aggregation\Terms('researchGrpId');
        $nestedAgg = new Aggregation\Nested('nested', 'researchGroup');
        $agg->setField('researchGroup.id');
        $agg->setSize(500);
        $nestedAgg->addAggregation($agg);
        $mainQuery->addAggregation($nestedAgg);
        if (isset($options['rgId'])) {
            $researchGroupNameQuery = new Query\Nested();
            $researchGroupNameQuery->setPath('researchGroup');
            $rgNamequery = new Query\Terms();
            $rgNamequery->setTerms('researchGroup.id', [$options['rgId']]);
            $researchGroupNameQuery->setQuery($rgNamequery);
            $filterBoolQuery->addFilter($researchGroupNameQuery);
        }

        $subMainQuery->addMust($fieldsBoolQuery);
        $subMainQuery->addMust($filterBoolQuery);

        $mainQuery->setQuery($subMainQuery);
        $mainQuery->setFrom(($page - 1) * 10);

        return $mainQuery;
    }

    /**
     * Get the paginator adapter for the query.
     *
     * @param string $queryTerm Query string.
     * @param array  $options   Options for the query.
     *
     * @return Pagerfanta
     */
    private function getPagiantor(string $queryTerm, array $options = []): Pagerfanta
    {
        $query = $this->buildQuery($queryTerm, 1, $options);

        $userPaginator = $this->finder->findPaginated($query);

        return $userPaginator;
    }

    /**
     * Get the aggregations for the query.
     *
     * @param string $queryTerm Query string.
     * @param array  $options   Options for the query.
     *
     * @return array
     */
    public function getAggregations(string $queryTerm, array $options = []): array
    {
        $userPaginator = $this->getPagiantor($queryTerm, $options);

        $aggs = array_column(
            $userPaginator->getAdapter()->getAggregations()['nested']['researchGrpId']['buckets'],
            'doc_count',
            'key'
        );

        return $aggs;
    }
}
