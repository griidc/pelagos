<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;

use Elastica\Aggregation;
use Elastica\Query;

use FOS\ElasticaBundle\Finder\TransformedFinder;

use Pagerfanta\Pagerfanta;

use App\Entity\DatasetSubmission;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\Person;
use App\Entity\ResearchGroup;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

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
     * The entity manager to use.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Elastic index mapping for title.
     */
    const ELASTIC_INDEX_MAPPING_TITLE = 'title';

    /**
     * Elastic index mapping for abstract.
     */
    const ELASTIC_INDEX_MAPPING_ABSTRACT = 'abstract';

    /**
     * Elastic index mapping for authors.
     */
    const ELASTIC_INDEX_MAPPING_AUTHORS = 'datasetSubmission.authors';

    /**
     * Elastic index mapping for theme keywords.
     */
    const ELASTIC_INDEX_MAPPING_THEME_KEYWORDS = 'datasetSubmission.themeKeywords';

    /**
     * Elastic index mapping for dataset DOI.
     */
    const ELASTIC_INDEX_MAPPING_DOI = 'doi.doi';

    /**
     * Elastic index mapping for udi.
     */
    const ELASTIC_INDEX_MAPPING_UDI = 'udi';

    /**
     * Elastic index mapping for sorting date used for displaying results.
     */
    const ELASTIC_INDEX_MAPPING_SORTING_DATE = 'sortingDateForDisplay';

    /**
     * Elastic index mapping for publication dois.
     */
    const ELASTIC_INDEX_MAPPING_PUB_DOI = 'publications.doi';

    const AVAILABILITY_STATUSES = array(
        1 => [DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE],
        2 => [DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION, DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL],
        3 => [DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED, DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED],
        4 => [DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE, DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED]
    );

    /**
     * Index boost for Title, Authors, Theme Keywords.
     */
    const BOOST = '^2';

    /**
     * Default value for aggregation size to get all aggregation terms.
     */
    const DEFAULT_AGGREGATION_TERM_SIZE = 99999;

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
     * @return array
     */
    public function findDatasets(Query $query): array
    {
        return $this->finder->findHybrid($query);
    }

    /**
     * Get number of results.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return integer
     */
    public function getCount(Query $query): int
    {
        return $this->finder->createPaginatorAdapter($query)->getTotalHits(true);
    }

    /**
     * Build Query using Fos Elastic search.
     *
     * @param array $requestTerms Options for the query.
     *
     * @return Query
     */
    public function buildQuery(array $requestTerms): Query
    {
        $page = ($requestTerms['page']) ? $requestTerms['page'] : 1;
        $queryTerm = $requestTerms['query'];
        $specificField = $requestTerms['field'];
        $perPage = $requestTerms['perPage'];
        $collectionDateRange = array();
        if ($requestTerms['collectionStartDate'] and $requestTerms['collectionEndDate']) {
            $collectionDateRange = array(
                'startDate' => $requestTerms['collectionStartDate'],
                'endDate' => $requestTerms['collectionEndDate']
            );
        }

        $mainQuery = new Query();

        $subMainQuery = $this->getSubMainQuery($queryTerm, $specificField, $collectionDateRange);

        // Add facet filters
        if (!empty($requestTerms['options']['funOrgId'])
            || !empty($requestTerms['options']['rgId'])
            || !empty($requestTerms['options']['status']
            || !empty($requestTerms['options']['fundingCycleId'])
            || !empty($requestTerms['options']['projectDirectorId']))
        ) {
            $mainQuery->setPostFilter($this->getFiltersQuery($requestTerms));
        }

        // Add nested agg for research group and funding org to main agg
        $mainQuery->addAggregation($this->getAggregationsQuery($requestTerms));

        // Add dataset availability status agg to mainQuery
        $mainQuery->addAggregation($this->getStatusAggregationQuery());

        // Add project director aggregation to the mainQuery
        $mainQuery->addAggregation($this->getProjectDirectorAggregationQuery());
        $mainQuery->setQuery($subMainQuery);

        // Add sort when search terms are not present
        if (empty($queryTerm)) {
            $mainQuery->addSort(array(self::ELASTIC_INDEX_MAPPING_SORTING_DATE => array('order' => 'desc')));
        }
        $mainQuery->setFrom(($page - 1) * 10);
        $mainQuery->setSize($perPage);

        return $mainQuery;
    }

    /**
     * Get the paginator adapter for the query.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return Pagerfanta
     */
    private function getPaginator(Query $query): Pagerfanta
    {
        return $this->finder->findPaginated($query);
    }

    /**
     * Get the aggregations for the query.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return array
     */
    public function getResearchGroupAggregations(Query $query): array
    {
        $userPaginator = $this->getPaginator($query);
        $researchGroupBucket = array_column(
            $this->findKey($userPaginator->getAdapter()->getAggregations(), 'researchGrpId')['buckets'],
            'doc_count',
            'key'
        );

        return $this->getResearchGroupsInfo($researchGroupBucket);
    }

    /**
     * Get research group information for the aggregations.
     *
     * @param array $aggregations Aggregations for each research id.
     *
     * @return array
     */
    private function getResearchGroupsInfo(array $aggregations): array
    {
        $researchGroupsInfo = array();

        $researchGroups = $this->entityManager
            ->getRepository(ResearchGroup::class)
            ->findBy(array('id' => array_keys($aggregations)));

        foreach ($researchGroups as $researchGroup) {
            $researchGroupsInfo[$researchGroup->getId()] = array(
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
                'shortName' => $researchGroup->getShortName(),
                'count' => $aggregations[$researchGroup->getId()]
            );
        }

        //Sorting based on highest count
        $array_column = array_column($researchGroupsInfo, 'count');
        array_multisort($array_column, SORT_DESC, $researchGroupsInfo);

        return $researchGroupsInfo;
    }

    /**
     * Get the aggregations for the query.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return array
     */
    public function getFundingOrgAggregations(Query $query): array
    {
        $userPaginator = $this->getPaginator($query);

        $fundingOrgBucket = array_column(
            $this->findKey($userPaginator->getAdapter()->getAggregations(), 'fundingOrgId')['buckets'],
            'doc_count',
            'key'
        );

        return $this->getFundingOrgInfo($fundingOrgBucket);
    }

    /**
     * Get funding org information for the aggregations.
     *
     * @param array $aggregations Aggregations for each funding org id.
     *
     * @return array
     */
    private function getFundingOrgInfo(array $aggregations): array
    {
        $fundingOrgInfo = array();

        $fundingOrgs = $this->entityManager
            ->getRepository(FundingOrganization::class)
            ->findBy(array('id' => array_keys($aggregations)));

        foreach ($fundingOrgs as $fundingOrg) {
            $fundingOrgInfo[$fundingOrg->getId()] = array(
                'id' => $fundingOrg->getId(),
                'name' => $fundingOrg->getName(),
                'shortName' => $fundingOrg->getShortName(),
                'count' => $aggregations[$fundingOrg->getId()]
            );
        }
        //Sorting based on highest count
        $array_column = array_column($fundingOrgInfo, 'count');
        array_multisort($array_column, SORT_DESC, $fundingOrgInfo);

        return $fundingOrgInfo;
    }

    /**
     * Get the aggregations for the query.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return array
     */
    public function getStatusAggregations(Query $query): array
    {
        $userPaginator = $this->getPaginator($query);

        $statusBucket = array_column(
            $this->findKey($userPaginator->getAdapter()->getAggregations(), 'status')['buckets'],
            'doc_count',
            'key'
        );

        return $this->getStatusInfo($statusBucket);
    }

    /**
     * Get dataset availability status information for the aggregations.
     *
     * @param array $aggregations Aggregations for each availability status.
     *
     * @return array
     */
    private function getStatusInfo(array $aggregations): array
    {
        $datasetCount = function ($status) use ($aggregations) {
            if (array_key_exists($status, $aggregations)) {
                return $aggregations[$status];
            } else {
                return 0;
            }
        };

        $statusInfo = [
            [
                'id' => 1,
                'name' => 'Identified',
                'count' => $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE)
            ],
            [
                'id' => 2,
                'name' => 'Submitted',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL)
                )
            ],
            [
                'id' => 3,
                'name' => 'Restricted',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED)
                )
            ],
            [
                'id' => 4,
                'name' => 'Available',
                'count' => (
                    $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)
                    + $datasetCount(DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE)
                )
            ],
        ];

        // Remove any element with a count of 0.
        foreach ($statusInfo as $key => $value) {
            if (0 === $value['count']) {
                unset($statusInfo[$key]);
            }
        }

        //Sorting based on highest count
        $array_column = array_column($statusInfo, 'count');
        array_multisort($array_column, SORT_DESC, $statusInfo);

        return $statusInfo;
    }

    /**
     * Get the funding cycle aggregations for the query.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return array
     */
    public function getFundingCycleAggregations(Query $query): array
    {
        $userPaginator = $this->getPaginator($query);
        $fundingCycleBucket = array_column(
            $this->findKey($userPaginator->getAdapter()->getAggregations(), 'fundingCycleId')['buckets'],
            'doc_count',
            'key'
        );

        return $this->getFundingCycleInfo($fundingCycleBucket);
    }

    /**
     * Get funding cycle information for the aggregations.
     *
     * @param array $aggregations Aggregations for each funding cycle id.
     *
     * @return array
     */
    private function getFundingCycleInfo(array $aggregations): array
    {
        $fundingCycleInfo = array();

        $fundingCycles = $this->entityManager
            ->getRepository(FundingCycle::class)
            ->findBy(array('id' => array_keys($aggregations)));

        foreach ($fundingCycles as $fundingCycle) {
            $fundingCycleInfo[$fundingCycle->getId()] = array(
                'id' => $fundingCycle->getId(),
                'name' => $fundingCycle->getName(),
                'count' => $aggregations[$fundingCycle->getId()]
            );
        }
        //Sorting based on highest count
        $array_column = array_column($fundingCycleInfo, 'count');
        array_multisort($array_column, SORT_DESC, $fundingCycleInfo);

        return $fundingCycleInfo;
    }

    /**
     * Get the project director aggregations for the query.
     *
     * @param Query $query The query built based on the search terms and parameters.
     *
     * @return array
     */
    public function getProjectDirectorAggregations(Query $query): array
    {
        $userPaginator = $this->getPaginator($query);
        $projectDirectorBucket = array_column(
            $this->findKey($userPaginator->getAdapter()->getAggregations(), 'projectDirectorId')['buckets'],
            'doc_count',
            'key'
        );

        return $this->getProjectDirectorInfo($projectDirectorBucket);
    }

    /**
     * Get project director information for the aggregations.
     *
     * @param array $aggregations Aggregations for each project director id.
     *
     * @return array
     */
    private function getProjectDirectorInfo(array $aggregations): array
    {
        $projectDirectorInfo = array();

        $people = $this->entityManager
            ->getRepository(Person::class)
            ->findBy(array('id' => array_keys($aggregations)));

        foreach ($people as $projectDirector) {
            $projectDirectorInfo[$projectDirector->getId()] = array(
                'id' => $projectDirector->getId(),
                'name' => $projectDirector->getLastName() . ', ' . $projectDirector->getFirstName(),
                'count' => $aggregations[$projectDirector->getId()]
            );
        }
        //Sorting based on highest count
        $array_column1 = array_column($projectDirectorInfo, 'count');
        $array_column2 = array_column($projectDirectorInfo, 'name');
        array_multisort(
            $array_column1,
            SORT_DESC,
            $array_column2,
            SORT_ASC,
            $projectDirectorInfo
        );

        return $projectDirectorInfo;
    }

    /**
     * Find the bucket name of the aggregation.
     *
     * @param array  $aggregations Array of aggregations.
     * @param string $bucketKey    The name of the bucket to be found.
     *
     * @return array
     */
    private function findKey(array $aggregations, string $bucketKey)
    {
        $bucket = array();

        //create a recursive iterator to loop over the array recursively
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($aggregations),
            RecursiveIteratorIterator::SELF_FIRST
        );

        //loop over the iterator
        foreach ($iterator as $key => $value) {
            //if the key matches our search
            if ($key === $bucketKey) {
                //add the current key
                $keys = array($key);
                //loop up the recursive chain
                for ($i = ($iterator->getDepth() - 1); $i >= 0; $i--) {
                    //add each parent key
                    array_unshift($keys, $iterator->getSubIterator($i)->key());
                }
                //return our output array
                $bucket = $value;
            }
        }
        return $bucket;
    }

    /**
     * Get Bool query for fields.
     *
     * @param string     $queryTerm           Query term that needs to be searched upon.
     * @param string     $specificField       Query a specific field for data.
     * @param array|null $collectionDateRange Query for collection date range.
     *
     * @return Query\BoolQuery
     */
    private function getFieldsQuery(string $queryTerm, string $specificField = null, array $collectionDateRange = null): Query\BoolQuery
    {
        // Bool query to add all fields
        $fieldsBoolQuery = new Query\BoolQuery();

        if ($specificField) {
            $specificFieldMatchQuery = new Query\Match();
            $specificFieldMatchQuery->setFieldQuery($specificField, $queryTerm);
            $specificFieldMatchQuery->setFieldOperator($specificField, 'and');
            $fieldsBoolQuery->addShould($specificFieldMatchQuery);
        } else {
            $queryTerm = $this->doesDoiExistInQueryTerm($queryTerm, $fieldsBoolQuery);
            $queryTerm = $this->doesUdiExistInQueryTerm($queryTerm, $fieldsBoolQuery);
            $fieldsMultiMatchQuery = new Query\MultiMatch();
            $fieldsMultiMatchQuery->setQuery($queryTerm);
            $fieldsMultiMatchQuery->setOperator('and');
            $fieldsMultiMatchQuery->setType('cross_fields');
            $fieldsMultiMatchQuery->setFields(
                [
                    self::ELASTIC_INDEX_MAPPING_TITLE . self::BOOST,
                    self::ELASTIC_INDEX_MAPPING_ABSTRACT,
                    self::ELASTIC_INDEX_MAPPING_THEME_KEYWORDS . self::BOOST,
                    self::ELASTIC_INDEX_MAPPING_AUTHORS . self::BOOST
                ]
            );
            $fieldsBoolQuery->addShould($fieldsMultiMatchQuery);
        }

        return $fieldsBoolQuery;
    }

    /**
     * Get aggregations for the query.
     *
     * @param array $requestTerms Options for the query.
     *
     * @return Aggregation\Nested
     */
    private function getAggregationsQuery(array $requestTerms): Aggregation\Nested
    {
        // Add nested field path for research group field
        $nestedRgAgg = new Aggregation\Nested('nestedResGrp', 'researchGroup');

        // Add researchGroup id field to the aggregation
        $researchGroupAgg = new Aggregation\Terms('researchGrpId');
        $researchGroupAgg->setField('researchGroup.id');
        $researchGroupAgg->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);

        if (!empty($requestTerms['options']['funOrgId'])) {
            $fundOrgFilter = new Aggregation\Filter('fundOrgFilter');
            $fundOrgNestedQuery = new Query\Nested();
            $fundOrgNestedQuery->setPath('researchGroup.fundingCycle.fundingOrganization');
            $fundOrgTerm = new Query\Terms('researchGroup.fundingCycle.fundingOrganization.id');
            $fundOrgTerm->setTerms(
                explode(',', $requestTerms['options']['funOrgId'])
            );
            $fundOrgNestedQuery->setQuery($fundOrgTerm);
            $fundOrgFilter->setFilter($fundOrgNestedQuery);
            $fundOrgFilter->addAggregation($researchGroupAgg);

            // Add research group agg to nested
            $nestedRgAgg->addAggregation($fundOrgFilter);
        } elseif (!empty($requestTerms['options']['fundingCycleId'])) {
            $fundingCycleFilter = new Aggregation\Filter('fundingCycleFilter');
            $fundingCycleNestedQuery = new Query\Nested();
            $fundingCycleNestedQuery->setPath('researchGroup.fundingCycle');
            $fundingCycleTerms = new Query\Terms('researchGroup.fundingCycle.id');
            $fundingCycleTerms->setTerms(
                explode(',', $requestTerms['options']['fundingCycleId'])
            );
            $fundingCycleNestedQuery->setQuery($fundingCycleTerms);
            $fundingCycleFilter->setFilter($fundingCycleNestedQuery);
            $fundingCycleFilter->addAggregation($researchGroupAgg);
            // Add research group agg to nested
            $nestedRgAgg->addAggregation($fundingCycleFilter);
        } else {
            $nestedRgAgg->addAggregation($researchGroupAgg);
        }

        // Add nested field path for funding cycle field
        $nestedFcAgg = new Aggregation\Nested('nestedFunCyc', 'researchGroup.fundingCycle');
        $fundingCycleTerms = new Aggregation\Terms('fundingCycleId');
        $fundingCycleTerms->setField('researchGroup.fundingCycle.id');
        $fundingCycleTerms->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);

        // Add nested field path for funding org field
        $nestedFoAgg = new Aggregation\Nested('nestedFunOrg', 'researchGroup.fundingCycle.fundingOrganization');
        // Add funding Org id field to the aggregation
        $fundingOrgAgg = new Aggregation\Terms('fundingOrgId');
        $fundingOrgAgg->setField('researchGroup.fundingCycle.fundingOrganization.id');
        $fundingOrgAgg->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);


        // Add funding Org agg to nested agg
        $nestedFoAgg->addAggregation($fundingOrgAgg);

        // Add funding org to funding cycle agg
        $nestedFcAgg->addAggregation($nestedFoAgg);

        // Add funding cycle terms to funding cycle agg
        $nestedFcAgg->addAggregation($fundingCycleTerms);
        // Add Nested fundingOrg agg to nested research group agg
        $nestedRgAgg->addAggregation($nestedFcAgg);

        return $nestedRgAgg;
    }

    /**
     * Get project director aggregations for the query.
     *
     * @return Aggregation\Nested
     */
    private function getProjectDirectorAggregationQuery(): Aggregation\Nested
    {
        // Add nested field path for project director field
        $projectDirectorAgg = new Aggregation\Nested('directors', 'projectDirectors');

        // Add project director id field to the aggregation
        $projectDirectorTerms = new Aggregation\Terms('projectDirectorId');
        $projectDirectorTerms->setField('projectDirectors.id');
        $projectDirectorTerms->setSize(self::DEFAULT_AGGREGATION_TERM_SIZE);

        return $projectDirectorAgg->addAggregation($projectDirectorTerms);
    }

    /**
     * Get status aggregations for the query.
     *
     * @return Aggregation\Terms
     */
    private function getStatusAggregationQuery(): Aggregation\Terms
    {
        $availabilityStatusAgg = new Aggregation\Terms('status');
        $availabilityStatusAgg->setField('availabilityStatus');
        $availabilityStatusAgg->setSize(5);

        return $availabilityStatusAgg;
    }

    /**
     * Get post filter query.
     *
     * @param array $requestTerms Options for the query.
     *
     * @return Query\BoolQuery
     */
    private function getFiltersQuery(array $requestTerms): Query\BoolQuery
    {
        // Bool query to add filters
        $filterBoolQuery = new Query\BoolQuery();
        $postFilterBoolQuery = new Query\BoolQuery();

        if (!empty($requestTerms['options']['rgId'])) {
            $researchGroupNameQuery = new Query\Nested();
            $researchGroupNameQuery->setPath('researchGroup');

            $rgNameQuery = new Query\Terms('researchGroup.id');
            $rgNameQuery->setTerms(explode(',', $requestTerms['options']['rgId']));
            $researchGroupNameQuery->setQuery($rgNameQuery);

            $postFilterBoolQuery->addMust($researchGroupNameQuery);
        }

        if (!empty($requestTerms['options']['funOrgId'])) {
            // Add nested field path for funding org field
            $nestedFoQuery = new Query\Nested();
            $nestedFoQuery->setPath('researchGroup.fundingCycle.fundingOrganization');

            // Add funding Org id field to the aggregation
            $fundingOrgIdQuery = new Query\Terms('researchGroup.fundingCycle.fundingOrganization.id');
            $fundingOrgIdQuery->setTerms(
                explode(',', $requestTerms['options']['funOrgId'])
            );

            $nestedFoQuery->setQuery($fundingOrgIdQuery);
            $postFilterBoolQuery->addMust($nestedFoQuery);
        }

        if (!empty($requestTerms['options']['status'])) {
            $statuses = array();
            foreach (explode(',', $requestTerms['options']['status']) as $key => $value) {
                $statuses[$key] = self::AVAILABILITY_STATUSES[$value];
            }

            $availabilityStatusQuery = new Query\Terms('availabilityStatus');
            $availabilityStatusQuery->setTerms(
                array_reduce($statuses, 'array_merge', array())
            );
            $postFilterBoolQuery->addMust($availabilityStatusQuery);
        }

        if (!empty($requestTerms['options']['fundingCycleId'])) {
            // Add nested field path for funding cycle field
            $fundingCycNestedQuery = new Query\Nested();
            $fundingCycNestedQuery->setPath('researchGroup.fundingCycle');

            // Add funding cycle id field to the aggregation
            $fundingCycleTerms = new Query\Terms('researchGroup.fundingCycle.id');
            $fundingCycleTerms->setTerms(
                explode(',', $requestTerms['options']['fundingCycleId'])
            );

            $fundingCycNestedQuery->setQuery($fundingCycleTerms);
            $postFilterBoolQuery->addMust($fundingCycNestedQuery);
        }

        if (!empty($requestTerms['options']['projectDirectorId'])) {
            // Add nested field path for project director field
            $projectDirectorNestedQuery = new Query\Nested();
            $projectDirectorNestedQuery->setPath('projectDirectors');

            // Add project director id field to the aggregation
            $projectDirectorTermsQuery = new Query\Terms('projectDirectors.id');
            $projectDirectorTermsQuery->setTerms(
                explode(',', $requestTerms['options']['projectDirectorId'])
            );

            $projectDirectorNestedQuery->setQuery($projectDirectorTermsQuery);
            $postFilterBoolQuery->addMust($projectDirectorNestedQuery);
        }
        $filterBoolQuery->addMust($postFilterBoolQuery);

        return $filterBoolQuery;
    }

    /**
     * Get query for exact match.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\QueryString
     */
    private function getExactMatchQuery(string $queryTerm): Query\QueryString
    {
        $exactMatchQuery = new Query\QueryString();
        $exactMatchQuery->setQuery($queryTerm);
        $exactMatchQuery->setDefaultOperator('and');

        return $exactMatchQuery;
    }

    /**
     * Added start date range for collection.
     *
     * @param array $collectionDates Data collection range start date.
     *
     * @return Query\Range
     */
    private function getCollectionStartDateQuery(array $collectionDates): Query\Range
    {
        $collectionStartDateRange = new Query\Range();
        $collectionStartDate = new \DateTime($collectionDates['startDate']);
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
    private function getCollectionEndDateQuery(array $collectionDates): Query\Range
    {
        $collectionEndDateRange = new Query\Range();
        $collectionEndDate = new \DateTime($collectionDates['endDate']);
        $collectionEndDateRange->addField('collectionEndDate', ['lte' => $collectionEndDate->format('Y-m-d H:i:s')]);

        return $collectionEndDateRange;
    }

    /**
     * To check if DOI exists in the search term.
     *
     * @param string          $queryTerm       Query term that needs to be checked if DOI exists.
     * @param Query\BoolQuery $fieldsBoolQuery The fields elastic boolean query that DOI query is added to.
     *
     * @return string
     */
    private function doesDoiExistInQueryTerm(string $queryTerm, Query\BoolQuery $fieldsBoolQuery): string
    {
        $doiRegEx = '!\b(?:[Dd][Oo][Ii]\s*:\s*)?(10.\d{4,9}/[-._;()/:A-Z0-9a-z]+)\b!';
        if (preg_match_all($doiRegEx, $queryTerm, $matches)) {
            trim(preg_replace($doiRegEx, '', $queryTerm));
            $queryTerm = $matches[1][0];
            $fieldsBoolQuery->addShould($this->getDoiQuery($queryTerm));
            $fieldsBoolQuery->addShould($this->getPubDoiQuery($queryTerm));
        }
        return $queryTerm;
    }

    /**
     * Get the DOI query.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\Nested
     */
    private function getDoiQuery(string $queryTerm): Query\Nested
    {
        // Query against dataset DOIs.
        $doiQuery = new Query\Nested();
        $doiQuery->setPath('doi');
        $doiNestedQuery = new Query\MatchPhrase();
        $doiNestedQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING_DOI, $queryTerm);
        $doiNestedQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING_DOI, 4);
        $doiQuery->setQuery($doiNestedQuery);
        return $doiQuery;
    }

    /**
     * Get the Publication doi query.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\Nested
     */
    private function getPubDoiQuery(string $queryTerm): Query\Nested
    {
        $pubDoiNestedQuery = new Query\Nested();
        $pubDoiNestedQuery->setPath('publications');
        $pubDoiQuery = new Query\MatchPhrase();
        $pubDoiQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING_PUB_DOI, $queryTerm);
        $pubDoiNestedQuery->setQuery($pubDoiQuery);
        return $pubDoiNestedQuery;
    }

    /**
     * Get the UDI query.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\MatchPhrase
     */
    private function getUdiQuery(string $queryTerm): Query\MatchPhrase
    {
        $udiQuery = new Query\MatchPhrase();
        $udiQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING_UDI, $queryTerm);
        $udiQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING_UDI, 4);

        return $udiQuery;
    }

    /**
     * To check if udi exists in the search term.
     *
     * @param string          $queryTerm       Query term that needs to be checked if udi exists.
     * @param Query\BoolQuery $fieldsBoolQuery The fields elastic boolean query that udi query is added to.
     *
     * @return string
     */
    private function doesUdiExistInQueryTerm(string $queryTerm, Query\BoolQuery $fieldsBoolQuery): string
    {
        $udiRegEx = '/\b([A-Z\d]{2}\.x\d\d\d\.\d\d\d[:.]\d\d\d\d)\b/i';
        if (preg_match_all($udiRegEx, $queryTerm, $matches)) {
            trim(preg_replace($udiRegEx, '', $queryTerm));
            $queryTerm = $matches[1][0];
            // Replacing the 11th position to ":"
            $queryTerm = substr_replace($queryTerm, ':', 11, 1);
            $fieldsBoolQuery->addShould($this->getUdiQuery($queryTerm));
        }
        return $queryTerm;
    }

    /**
     * Split the query terms into must match and must not match terms.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return array
     */
    private function splitQueryTerms(string $queryTerm): array
    {
        $splitUpQueryTerms = array();
        if (preg_match_all('/(?:\s(?<!\b)|^)-\b(\w*)\b/', $queryTerm, $matches)) {
            $splitUpQueryTerms = array(
                'mustNotMatch' => '',
                'mustMatch' => ''
            );
            $splitUpQueryTerms['mustMatch'] = str_replace($matches[0], '', $queryTerm);
            $splitUpQueryTerms['mustNotMatch'] = $matches[1];
        }

        return $splitUpQueryTerms;
    }

    /**
     * Get must not include terms query.
     *
     * @param string $mustNotQueryTerm Query term that needs to be searched upon.
     *
     * @return Query\MultiMatch
     */
    private function getMustNotIncludeTermsQuery(string $mustNotQueryTerm): Query\MultiMatch
    {
        $mustNotMultiMatch = new Query\MultiMatch();
        $mustNotMultiMatch->setFields(
            [
                self::ELASTIC_INDEX_MAPPING_ABSTRACT,
                self::ELASTIC_INDEX_MAPPING_TITLE,
                self::ELASTIC_INDEX_MAPPING_THEME_KEYWORDS,
                self::ELASTIC_INDEX_MAPPING_AUTHORS
            ]
        );
        $mustNotMultiMatch->setQuery($mustNotQueryTerm);

        return $mustNotMultiMatch;
    }

    /**
     * Get sub main query.
     *
     * @param string|null $queryTerm           Query term that needs to be searched upon.
     * @param string|null $specificField       Specific field option to filter the results.
     * @param array|null  $collectionDateRange Date range option to filter the results.
     *
     * @return Query\BoolQuery
     */
    private function getSubMainQuery(string $queryTerm = null, string $specificField = null, array $collectionDateRange = null): Query\BoolQuery
    {
        // Bool query to combine field query and filter query
        $subMainQuery = new Query\BoolQuery();

        // Bool query to get range temporal extent dates
        $collectionDateBoolQuery = new Query\BoolQuery();

        if ($queryTerm) {
            // Check if exclude term exists in the given query term
            $mustNotQueryTerms = '';
            $splitUpQueryTerms = $this->splitQueryTerms($queryTerm);
            if (!empty($splitUpQueryTerms)) {
                $queryTerm = $splitUpQueryTerms['mustMatch'];
                $mustNotQueryTerms = $splitUpQueryTerms['mustNotMatch'];
            }

            // If exclude term exists add the must not query
            if (!empty($mustNotQueryTerms)) {
                foreach ($mustNotQueryTerms as $mustNotQueryTerm) {
                    $mustNotBoolQuery = $this->getMustNotIncludeTermsQuery($mustNotQueryTerm);
                    $subMainQuery->addMustNot($mustNotBoolQuery);
                }
            }

            // Search exact phrase if query string has double quotes
            if (preg_match('/"/', $queryTerm)) {
                $subMainQuery->addMust($this->getExactMatchQuery($queryTerm));
            } else {
                $subMainQuery->addMust($this->getFieldsQuery($queryTerm, $specificField, $collectionDateRange));
            }
        } else {
            $allDatasetsQuery = new Query\Term();
            $allDatasetsQuery->setTerm('identifiedStatus', 2);
            $subMainQuery->addMust($allDatasetsQuery);
        }

        if (!empty($collectionDateRange)) {
            $collectionDateBoolQuery->addMust($this->getCollectionStartDateQuery($collectionDateRange));
            $collectionDateBoolQuery->addMust($this->getCollectionEndDateQuery($collectionDateRange));
            $subMainQuery->addFilter($collectionDateBoolQuery);
        }

        return $subMainQuery;
    }
}
