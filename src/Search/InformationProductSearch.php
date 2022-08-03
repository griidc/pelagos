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
class InformationProductSearch
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

//        $this->addFilters($boolQuery, $searchOptions);

        $query = new Query();
        $query->setQuery($boolQuery);

        $query->setPostFilter($this->addResearchGroupFilter($searchOptions));

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
//         $publishedQueryTerm = new Term();
//         $publishedQueryTerm->setTerm('published', $searchOptions->shouldFilterOnlyPublishedInformationProducts());
//         $boolQuery->addFilter($publishedQueryTerm);

        $researchGroupNameQuery = new Query\Nested();
        $researchGroupNameQuery->setPath('researchGroup');

        if ($searchOptions->isResearchGroupFilterSet()) {
            $researchGroupQueryTerm = new Query\Terms('researchGroup.id');
            $researchGroupQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
            $researchGroupNameQuery->setQuery($researchGroupQueryTerm);
            $boolQuery->addFilter($researchGroupNameQuery);
        }

        // $productTypeDescNameQuery = new Query\Nested();
        // $productTypeDescNameQuery->setPath('productTypeDescriptors');

        // if ($searchOptions->isProductTypeDescFilterSet()) {
        //     $productTypeDescQueryTerm = new Query\Terms('productTypeDescriptors.id');
        //     $productTypeDescQueryTerm->setTerms($searchOptions->getProductTypeDescFilter());
        //     $productTypeDescNameQuery->setQuery($productTypeDescQueryTerm);
        //     $boolQuery->addFilter($productTypeDescNameQuery);
        // }

        // $digitalTypeDescNameQuery = new Query\Nested();
        // $digitalTypeDescNameQuery->setPath('digitalResourceTypeDescriptors');

        // if ($searchOptions->isDigitalTypeDescFilterSet()) {
        //     $digitalTypeDescQueryTerm = new Query\Terms('digitalResourceTypeDescriptors.id');
        //     $digitalTypeDescQueryTerm->setTerms($searchOptions->getDigitalTypeDescFilter());
        //     $digitalTypeDescNameQuery->setQuery($digitalTypeDescQueryTerm);
        //     $boolQuery->addFilter($digitalTypeDescNameQuery);
        // }
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
        // foreach ($searchOptions->getFacets() as $facet) {
        //     $nestedAggregation = new AggregationNested("$facet.Agg", $facet);
        //     $aggregation = new AggregationTerms($facet);
        //     $aggregation->setField("$facet.id");
        //     $aggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        //     $nestedAggregation->addAggregation($aggregation);
        //     $query->addAggregation($nestedAggregation);
        // }

        $researchGroupNestedAggregation = new AggregationNested('researchGroupsAgg', 'info_blah.researchGroups');
        $researchGroupAggregation = new AggregationTerms('research_group_aggregation');
        $researchGroupAggregation->setField('researchGroups.id');
        $researchGroupAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $researchGroupNestedAggregation->addAggregation($researchGroupAggregation);

        // $researchGroupsAggregation = new AggregationTerms('research_group_aggregation');
        // $researchGroupsAggregation->setField('researchGroup.id');
        // $researchGroupsAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        // $researchGroupNestedAggregation->addAggregation($researchGroupsAggregation);

        $query->addAggregation($researchGroupNestedAggregation);

        $researchGroupNestedAggregationa = new AggregationNested('researchGroupAgg', 'search_blah.researchGroup');
        $researchGroupsAggregation = new AggregationTerms('research_group_aggregation');
        $researchGroupsAggregation->setField('researchGroup.id');
        $researchGroupsAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $researchGroupNestedAggregationa->addAggregation($researchGroupsAggregation);

        // $researchGroupsAggregation = new AggregationTerms('research_group_aggregation');
        // $researchGroupsAggregation->setField('researchGroup.id');
        // $researchGroupsAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        // $researchGroupNestedAggregation->addAggregation($researchGroupsAggregation);

        $query->addAggregation($researchGroupNestedAggregationa);

        // $productTypeNestedAggregation = new AggregationNested('productTypeDescriptorsAgg', 'productTypeDescriptors');
        // $productTypeDescriptorAggregation = new AggregationTerms('product_type_aggregation');
        // $productTypeDescriptorAggregation->setField('productTypeDescriptors.id');
        // $productTypeDescriptorAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        // $productTypeNestedAggregation->addAggregation($productTypeDescriptorAggregation);
        // $query->addAggregation($productTypeNestedAggregation);

        // $digitalResourceTypeNestedAggregation = new AggregationNested('digitalResourceTypeDescriptorsAgg', 'digitalResourceTypeDescriptors');
        // $digitalResourceTypeDescriptorAggregation = new AggregationTerms('digital_resource_aggregation');
        // $digitalResourceTypeDescriptorAggregation->setField('digitalResourceTypeDescriptors.id');
        // $digitalResourceTypeDescriptorAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        // $digitalResourceTypeNestedAggregation->addAggregation($digitalResourceTypeDescriptorAggregation);
        // $query->addAggregation($digitalResourceTypeNestedAggregation);
    }
}
