<?php

namespace App\Search;

use Doctrine\ORM\EntityManagerInterface;
use Elastica\Aggregation\Nested as AggregationNested;
use Elastica\Aggregation\Terms as AggregationTerms;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\Term;
use FOS\ElasticaBundle\Finder\TransformedFinder;

/**
 * Util class for FOS Elastic Search.
 */
class MultiSearch
{
    /**
     * Default value for aggregation size to get all aggregation terms.
     */
    const DEFAULT_AGGREGATION_TERM_SIZE = 99999;

    /**
     * FOS Elastica Object to find elastica documents.
     *
     * @var TransformedFinder
     */
    protected $finder;

    /**
     * Instance of the EntityManager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param SearchRepository       $finder        The finder interface object.
     * @param EntityManagerInterface $entityManager An entity manager.
     */
    public function __construct(SearchRepository $finder, EntityManagerInterface $entityManager)
    {
        $this->finder = $finder;
        $this->entityManager = $entityManager;
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

        $simpleQuery = new SimpleQueryString($queryString);
        $simpleQuery->setDefaultOperator(SimpleQueryString::OPERATOR_AND);

        $boolQuery = new BoolQuery();
        $boolQuery->addMust($simpleQuery);

        $query = new Query();
        $query->setQuery($boolQuery);

        if (!empty($searchOptions->getDataType())) {
            $publishedQueryTerm = new Term();
            $publishedQueryTerm->setTerm('friendlyName', $searchOptions->getDataType());
            $boolQuery->addMust($publishedQueryTerm);
        }

        if ($searchOptions->isResearchGroupFilterSet()) {
            $query->setPostFilter($this->addResearchGroupFilter($searchOptions));
        }
        $this->addAggregators($query, $searchOptions);

        $resultsPaginator = $this->finder->findPaginated($query);

        return new SearchResults($resultsPaginator, $searchOptions, $this->entityManager);
    }

    /**
     * Added research group filter.
     *
     * @param SearchOptions $searchOptions
     *
     * @return BoolQuery
     */
    private function addResearchGroupFilter(SearchOptions $searchOptions): BoolQuery
    {
        $researchFilterBoolQuery = new Query\BoolQuery();

        // Dataset Research Group Filter
        $datasetResearchGrpNameQuery = new Query\Nested();
        $datasetResearchGrpNameQuery->setPath('researchGroups');
        $datasetResearchGrpQueryTerm = new Query\Terms('researchGroups.id');
        $datasetResearchGrpQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
        $datasetResearchGrpNameQuery->setQuery($datasetResearchGrpQueryTerm);
        $datasetResearchGrpNameQuery->setParam('ignore_unmapped', true);
        $researchFilterBoolQuery->addShould($datasetResearchGrpNameQuery);

        // Information Product Research Group Filter
        $researchGroupsNameQuery = new Query\Nested();
        $researchGroupsNameQuery->setPath('researchGroup');
        $researchGroupsQueryTerm = new Query\Terms('researchGroup.id');
        $researchGroupsQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
        $researchGroupsNameQuery->setQuery($researchGroupsQueryTerm);
        $researchGroupsNameQuery->setParam('ignore_unmapped', true);
        $researchFilterBoolQuery->addShould($researchGroupsNameQuery);

        return $researchFilterBoolQuery;
    }

    /**
     * Add facet filters to search query.
     *
     * @param BoolQuery     $boolQuery     Bool query for search.
     * @param SearchOptions $searchOptions Options containing facet filters.
     *
     * @return void
     */
    private function addFilters(BoolQuery $boolQuery, SearchOptions $searchOptions): void
    {
        $researchGroupNameQuery = new Query\Nested();
        $researchGroupNameQuery->setPath('researchGroup');

        if ($searchOptions->isResearchGroupFilterSet()) {
            $researchGroupQueryTerm = new Query\Terms('researchGroup.id');
            $researchGroupQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
            $researchGroupNameQuery->setQuery($researchGroupQueryTerm);
            $boolQuery->addFilter($researchGroupNameQuery);
        }
    }

    /**
     * Add specific aggregators to the query.
     *
     * @param Query $query The query to have aggregators added to.
     *
     * @return void
     */
    private function addAggregators(Query $query, SearchOptions $searchOptions): void
    {
        $researchGroupNestedAggregation = new AggregationNested('researchGroupsAgg', 'researchGroups');
        $researchGroupAggregation = new AggregationTerms('research_groups_aggregation');
        $researchGroupAggregation->setField('researchGroups.id');
        $researchGroupAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $researchGroupNestedAggregation->addAggregation($researchGroupAggregation);

        $query->addAggregation($researchGroupNestedAggregation);

        $researchGroupNestedAggregationa = new AggregationNested('researchGroupAgg', 'researchGroup');
        $researchGroupsAggregation = new AggregationTerms('research_group_aggregation');
        $researchGroupsAggregation->setField('researchGroup.id');
        $researchGroupsAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $researchGroupNestedAggregationa->addAggregation($researchGroupsAggregation);

        $friendlyNameAggregation = new AggregationTerms('friendly_name_agregation');
        $friendlyNameAggregation->setField('friendlyName');
        $friendlyNameAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $query->addAggregation($friendlyNameAggregation);
    }
}
