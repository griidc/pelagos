<?php

namespace App\Search;

use Doctrine\ORM\EntityManagerInterface;
use Elastica\Aggregation\Nested as AggregationNested;
use Elastica\Aggregation\Terms as AggregationTerms;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
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
     * @param TransformedFinder      $finder        The finder interface object.
     * @param EntityManagerInterface $entityManager An entity manager.
     */
    public function __construct(TransformedFinder $finder, EntityManagerInterface $entityManager)
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

        if ($searchOptions->shouldFilterOnlyPublishedInformationProducts()) {
            $publishedQueryTerm = new Term();
            $publishedQueryTerm->setTerm('published', true);
            $boolQuery->addFilter($publishedQueryTerm);
        }

        $query = new Query();
        $query->setQuery($boolQuery);
        $query->setPostFilter($this->getPostFilters($searchOptions));

        $this->addAggregators($query);

        $resultsPaginator = $this->finder->findPaginated($query);

        return new SearchResults($resultsPaginator, $searchOptions, $this->entityManager);
    }

    /**
     * Add facet filters to search query.
     *
     * @param BoolQuery     $boolQuery     Bool query for search.
     * @param SearchOptions $searchOptions Options containing facet filters.
     *
     * @return AbstractQuery
     */
    private function getPostFilters(SearchOptions $searchOptions): AbstractQuery
    {
        $boolQuery = new BoolQuery();

        if ($searchOptions->shouldFilterOnlyPublishedInformationProducts()) {
            $publishedQueryTerm = new Term();
            $publishedQueryTerm->setTerm('published', true);
            $boolQuery->addFilter($publishedQueryTerm);
        }

        $researchGroupNameQuery = new Query\Nested();
        $researchGroupNameQuery->setPath('researchGroups');

        if ($searchOptions->isResearchGroupFilterSet()) {
            $researchGroupQueryTerm = new Query\Terms('researchGroups.id');
            $researchGroupQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
            $researchGroupNameQuery->setQuery($researchGroupQueryTerm);
            $boolQuery->addMust($researchGroupNameQuery);
        }

        $productTypeDescNameQuery = new Query\Nested();
        $productTypeDescNameQuery->setPath('productTypeDescriptors');

        if ($searchOptions->isProductTypeDescFilterSet()) {
            $productTypeDescQueryTerm = new Query\Terms('productTypeDescriptors.id');
            $productTypeDescQueryTerm->setTerms($searchOptions->getProductTypeDescFilter());
            $productTypeDescNameQuery->setQuery($productTypeDescQueryTerm);
            $boolQuery->addMust($productTypeDescNameQuery);
        }

        $digitalTypeDescNameQuery = new Query\Nested();
        $digitalTypeDescNameQuery->setPath('digitalResourceTypeDescriptors');

        if ($searchOptions->isDigitalTypeDescFilterSet()) {
            $digitalTypeDescQueryTerm = new Query\Terms('digitalResourceTypeDescriptors.id');
            $digitalTypeDescQueryTerm->setTerms($searchOptions->getDigitalTypeDescFilter());
            $digitalTypeDescNameQuery->setQuery($digitalTypeDescQueryTerm);
            $boolQuery->addMust($digitalTypeDescNameQuery);
        }


        return $boolQuery;
    }

    /**
     * Add specific aggregators to the query.
     *
     * @param Query $query The query to have aggregators added to.
     *
     * @return void
     */
    private function addAggregators(Query $query): void
    {
        $researchGroupNestedAggregation = new AggregationNested('researchGroupsAgg', 'researchGroups');
        $researchGroupAggregation = new AggregationTerms('research_group_aggregation');
        $researchGroupAggregation->setField('researchGroups.id');
        $researchGroupAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $researchGroupNestedAggregation->addAggregation($researchGroupAggregation);
        $query->addAggregation($researchGroupNestedAggregation);

        $productTypeNestedAggregation = new AggregationNested('productTypeDescriptorsAgg', 'productTypeDescriptors');
        $productTypeDescriptorAggregation = new AggregationTerms('product_type_aggregation');
        $productTypeDescriptorAggregation->setField('productTypeDescriptors.id');
        $productTypeDescriptorAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $productTypeNestedAggregation->addAggregation($productTypeDescriptorAggregation);
        $query->addAggregation($productTypeNestedAggregation);

        $digitalResourceTypeNestedAggregation = new AggregationNested('digitalResourceTypeDescriptorsAgg', 'digitalResourceTypeDescriptors');
        $digitalResourceTypeDescriptorAggregation = new AggregationTerms('digital_resource_aggregation');
        $digitalResourceTypeDescriptorAggregation->setField('digitalResourceTypeDescriptors.id');
        $digitalResourceTypeDescriptorAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $digitalResourceTypeNestedAggregation->addAggregation($digitalResourceTypeDescriptorAggregation);
        $query->addAggregation($digitalResourceTypeNestedAggregation);
    }
}
