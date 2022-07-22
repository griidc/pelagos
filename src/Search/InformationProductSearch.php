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
        $isPublished = $searchOptions->shouldFilterOnlyPublishedInformationProducts();

        $simpleQuery = new SimpleQueryString($queryString);
        $simpleQuery->setDefaultOperator(SimpleQueryString::OPERATOR_AND);

        $boolQuery = new BoolQuery();
        $boolQuery->addMust($simpleQuery);

        $publishedQueryTerm = new Term();
        $publishedQueryTerm->setTerm('published', $isPublished);
        $boolQuery->addFilter($publishedQueryTerm);

        $researchGroupNameQuery = new Query\Nested();
        $researchGroupNameQuery->setPath('researchGroups');

        if ($searchOptions->isResearchGroupFilterSet()) {
            $researchGroupQueryTerm = new Query\Terms('researchGroups.id');
            $researchGroupQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
            $researchGroupNameQuery->setQuery($researchGroupQueryTerm);
            $boolQuery->addFilter($researchGroupNameQuery);
        }

        $query = new Query();

        $query->setQuery($boolQuery);

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

        $resultsPaginator = $this->finder->findPaginated($query);

        return new SearchResults($resultsPaginator, $searchOptions, $this->entityManager);
    }
}
