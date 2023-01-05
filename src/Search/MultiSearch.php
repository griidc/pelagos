<?php

namespace App\Search;

use App\Entity\DatasetSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Aggregation\Nested as AggregationNested;
use Elastica\Aggregation\Terms as AggregationTerms;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Range;
use Elastica\Query\SimpleQueryString;
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
     * Mapped values for dataset availability statuses.
     */
    const AVAILABILITY_STATUSES = array(
        1 => [DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE],
        2 => [DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION, DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL],
        3 => [DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED, DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED],
        4 => [DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE, DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED]
    );

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

        $postBoolQuery = new BoolQuery();

        // Collection Date Filter
        if ($searchOptions->getDateType() === SearchOptions::DATE_TYPE_COLLECTION) {
            // Bool query to get range temporal extent dates
            $collectionDateBoolQuery = new Query\BoolQuery();
            if (!empty($searchOptions->getRangeStartDate())) {
                $collectionDateBoolQuery->addMust($this->getCollectionStartDateQuery($searchOptions->getRangeStartDate()));
            }
            if (!empty($searchOptions->getRangeEndDate())) {
                $collectionDateBoolQuery->addMust($this->getCollectionEndDateQuery($searchOptions->getRangeEndDate()));
            }
            $boolQuery->addFilter($collectionDateBoolQuery);
        }

        // Published Date Filter
        if (
            $searchOptions->getDateType() === SearchOptions::DATE_TYPE_PUBLISHED &&
            $searchOptions->getRangeEndDate() &&
            $searchOptions->getRangeStartDate()
        ) {
            $boolQuery->addFilter($this->getPublishedDateRangeQuery($searchOptions->getRangeStartDate(), $searchOptions->getRangeEndDate()));
        }

        if (!empty($searchOptions->getDataType())) {
            $friendlyNameQueryTerm = new Query\Terms('friendlyName');
            $friendlyNameQueryTerm->setTerms($searchOptions->getDataType());
            $postBoolQuery->addMust($friendlyNameQueryTerm);
        }

        if (!empty($searchOptions->getDatasetStatus())) {
            $statuses = array();
            foreach ($searchOptions->getDatasetStatus() as $key => $value) {
                $statuses[$key] = self::AVAILABILITY_STATUSES[$value];
            }

            $availabilityStatusQuery = new Query\Terms('availabilityStatus');
            $availabilityStatusQuery->setTerms(
                array_reduce($statuses, 'array_merge', array())
            );
            $postBoolQuery->addMust($availabilityStatusQuery);
        }

        if (!empty($searchOptions->getTags())) {
            $tagsQuery = new Query\Terms('tags');
            $tagsQuery->setTerms(
                $searchOptions->getTags()
            );
            $postBoolQuery->addMust($tagsQuery);
        }

        if ($searchOptions->isResearchGroupFilterSet()) {
            $postBoolQuery->addMust($this->addResearchGroupFilter($searchOptions));
        }

        if ($searchOptions->isFundingOrgFilterSet()) {
            $postBoolQuery->addMust($this->addFundingOrgFilter($searchOptions));
        }

        $query->setQuery($boolQuery);
        $query->setPostFilter($postBoolQuery);

        // Add sort order
        if ($searchOptions->getSortOrder() !== 'default') {
            $query->addSort(array('publishedDate' => array('order' => $searchOptions->getSortOrder())));
        }
        
        $this->addAggregators($query, $searchOptions);
        $resultsPaginator = $this->finder->findPaginated($query);
        return new SearchResults($resultsPaginator, $searchOptions, $this->entityManager);
    }

    /**
     * Returns the query for research group filtering.
     *
     * @param SearchOptions $searchOptions
     *
     * @return BoolQuery
     */
    private function addResearchGroupFilter(SearchOptions $searchOptions): BoolQuery
    {
        $researchFilterBoolQuery = new Query\BoolQuery();

        // Dataset Research Group Filter
        $datasetFundingOrgNameQuery = new Query\Nested();
        $datasetFundingOrgNameQuery->setPath('researchGroups');
        $datasetFundingOrgQueryTerm = new Query\Terms('researchGroups.id');
        $datasetFundingOrgQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
        $datasetFundingOrgNameQuery->setQuery($datasetFundingOrgQueryTerm);
        $datasetFundingOrgNameQuery->setParam('ignore_unmapped', true);
        $researchFilterBoolQuery->addShould($datasetFundingOrgNameQuery);

        // Information Product Research Group Filter
        $fundingOrgsNameQuery = new Query\Nested();
        $fundingOrgsNameQuery->setPath('researchGroup');
        $fundingOrgsQueryTerm = new Query\Terms('researchGroup.id');
        $fundingOrgsQueryTerm->setTerms($searchOptions->getResearchGroupFilter());
        $fundingOrgsNameQuery->setQuery($fundingOrgsQueryTerm);
        $fundingOrgsNameQuery->setParam('ignore_unmapped', true);
        $researchFilterBoolQuery->addShould($fundingOrgsNameQuery);

        return $researchFilterBoolQuery;
    }

    /**
     * Returns the query for funding org filtering.
     *
     * @param SearchOptions $searchOptions
     *
     * @return BoolQuery
     */
    private function addFundingOrgFilter(SearchOptions $searchOptions): BoolQuery
    {
        $fundingOrgFilterBoolQuery = new Query\BoolQuery();

        // Dataset Funding Org Filter
        $datasetFundingOrgNameQuery = new Query\Nested();
        $datasetFundingOrgNameQuery->setPath('researchGroup.fundingCycle.fundingOrganization');
        $datasetFundingOrgQueryTerm = new Query\Terms('researchGroup.fundingCycle.fundingOrganization.id');
        $datasetFundingOrgQueryTerm->setTerms($searchOptions->getFundingOrgFilter());
        $datasetFundingOrgNameQuery->setQuery($datasetFundingOrgQueryTerm);
        $datasetFundingOrgNameQuery->setParam('ignore_unmapped', true);
        $fundingOrgFilterBoolQuery->addShould($datasetFundingOrgNameQuery);

        // Information Product Funding Org Filter
        $fundingOrgsNameQuery = new Query\Nested();
        $fundingOrgsNameQuery->setPath('researchGroups.fundingCycle.fundingOrganization');
        $fundingOrgsQueryTerm = new Query\Terms('researchGroups.fundingCycle.fundingOrganization.id');
        $fundingOrgsQueryTerm->setTerms($searchOptions->getFundingOrgFilter());
        $fundingOrgsNameQuery->setQuery($fundingOrgsQueryTerm);
        $fundingOrgsNameQuery->setParam('ignore_unmapped', true);
        $fundingOrgFilterBoolQuery->addShould($fundingOrgsNameQuery);

        return $fundingOrgFilterBoolQuery;
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
        $query->addAggregation($researchGroupNestedAggregationa);

        $nestedFoAgg = new AggregationNested('fundingOrgAgg', 'researchGroup.fundingCycle.fundingOrganization');
        $fundingOrgAgg = new AggregationTerms('funding_organization_aggregation');
        $fundingOrgAgg->setField('researchGroup.fundingCycle.fundingOrganization.id');
        $fundingOrgAgg->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $nestedFoAgg->addAggregation($fundingOrgAgg);
        $query->addAggregation($nestedFoAgg);

        $nestedFoAgg = new AggregationNested('fundingOrgsAgg', 'researchGroups.fundingCycle.fundingOrganization');
        $fundingOrgAgg = new AggregationTerms('funding_organizations_aggregation');
        $fundingOrgAgg->setField('researchGroups.fundingCycle.fundingOrganization.id');
        $fundingOrgAgg->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $nestedFoAgg->addAggregation($fundingOrgAgg);
        $query->addAggregation($nestedFoAgg);

        $friendlyNameAggregation = new AggregationTerms('friendly_name_agregation');
        $friendlyNameAggregation->setField('friendlyName');
        $friendlyNameAggregation->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);
        $query->addAggregation($friendlyNameAggregation);

        $availabilityStatusAgg = new AggregationTerms('status');
        $availabilityStatusAgg->setField('availabilityStatus');
        $availabilityStatusAgg->setSize(5);
        $query->addAggregation($availabilityStatusAgg);

        $tagsAgg = new AggregationTerms('tags_agg');
        $tagsAgg->setField('tags');
        $tagsAgg->setSize(5);
        $query->addAggregation($tagsAgg);
    }

        /**
     * Added start date range for collection.
     *
     * @param array $collectionDates Data collection range start date.
     *
     * @return Query\Range
     */
    private function getCollectionStartDateQuery(string $collectionStartDate): Range
    {
        $collectionStartDateRange = new Range();
        $collectionStartDate = new \DateTime($collectionStartDate);
        $collectionStartDateRange->addField('collectionStartDate', ['gte' => $collectionStartDate->format('Y-m-d H:i:s')]);

        return $collectionStartDateRange;
    }

    /**
     * Added end date range for collection.
     *
     * @param array $collectionDates Data collection range end date.
     *
     * @return Query\Range
     */
    private function getCollectionEndDateQuery(string $collectionEndDate): Range
    {
        $collectionEndDateRange = new Range();
        $collectionEndDate = new \DateTime($collectionEndDate);
        $collectionEndDateRange->addField('collectionEndDate', ['lte' => $collectionEndDate->format('Y-m-d H:i:s')]);

        return $collectionEndDateRange;
    }

    /**
     * Get published date range query.
     *
     * @param string $publishedStartDate Published range start date.
     * @param string $publishedEndDate   Published range end date.
     *
     * @return BoolQuery
     */
    private function getPublishedDateRangeQuery(string $publishedStartDate, string $publishedEndDate): BoolQuery
    {
        $publishedDateBoolQuery = new BoolQuery();
        $publishedStartDate = new \DateTime($publishedStartDate);
        $publishedEndDate = new \DateTime($publishedEndDate);

        $publishedDateRange = new Range();
        $publishedDateRange->addField(
            'publishedDate',
            [
                'gte' => $publishedStartDate->format('Y-m-d'),
                'lte' => $publishedEndDate->format('Y-m-d')
            ]
        );
        $publishedDateBoolQuery->addShould($publishedDateRange);

        return $publishedDateBoolQuery;
    }
}
