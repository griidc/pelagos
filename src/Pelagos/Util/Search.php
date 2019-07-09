<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

use Elastica\Aggregation;
use Elastica\Query;

use FOS\ElasticaBundle\Finder\TransformedFinder;

use Pagerfanta\Pagerfanta;

use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\ResearchGroup;

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
     * @var EntityManager
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
     * Constructor.
     *
     * @param TransformedFinder $finder        The finder interface object.
     * @param EntityManager     $entityManager An entity manager.
     */
    public function __construct(TransformedFinder $finder, EntityManager $entityManager)
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
        return $this->finder->find($query);
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
        return $this->getPaginator($query)->getNbResults();
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

        $mainQuery = new Query();

        // Bool query to combine field query and filter query
        $subMainQuery = new Query\BoolQuery();

        // Search exact phrase if query string has double quotes
        if (preg_match('/"/', $queryTerm)) {
            $subMainQuery->addMust($this->getExactMatchQuery($queryTerm));
        } else {
            $subMainQuery->addMust($this->getFieldsQuery($queryTerm, $specificField));
        }

        // Add facet filters
        if (!empty($requestTerms['options']['funOrgId']) || !empty($requestTerms['options']['rgId'])) {
            $mainQuery->setPostFilter($this->getFiltersQuery($requestTerms));
        }

        // Add nested agg to main agg
        $mainQuery->addAggregation($this->getAggregationsQuery($requestTerms));

        $mainQuery->setQuery($subMainQuery);
        $mainQuery->setFrom(($page - 1) * 10);

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
                'count' => $aggregations[$researchGroup->getId()]
            );
        }

        //Sorting based on highest count
        array_multisort(array_column($researchGroupsInfo, 'count'), SORT_DESC, $researchGroupsInfo);

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
                'count' => $aggregations[$fundingOrg->getId()]
            );
        }

        //Sorting based on highest count
        array_multisort(array_column($fundingOrgInfo, 'count'), SORT_DESC, $fundingOrgInfo);

        return $fundingOrgInfo;
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
     * @param string $queryTerm     Query term that needs to be searched upon.
     * @param string $specificField Query a specific field for data.
     *
     * @return Query\BoolQuery
     */
    private function getFieldsQuery(string $queryTerm, string $specificField = null): Query\BoolQuery
    {
        // Bool query to add all fields
        $fieldsBoolQuery = new Query\BoolQuery();

        // Create nested for datasetSubmission fields
        $datasetSubmissionQuery = new Query\Nested();
        $datasetSubmissionQuery->setPath('datasetSubmission');

        // Bool query to add fields in datasetSubmission
        $datasetSubmissionBoolQuery = new Query\BoolQuery();
        if ($specificField) {
            if ($specificField === self::ELASTIC_INDEX_MAPPING_TITLE) {
                $fieldsBoolQuery->addShould($this->getTitleQuery($queryTerm));
            } elseif ($specificField === self::ELASTIC_INDEX_MAPPING_ABSTRACT) {
                $fieldsBoolQuery->addShould($this->getAbstractQuery($queryTerm));
            } elseif ($specificField === self::ELASTIC_INDEX_MAPPING_AUTHORS) {
                $datasetSubmissionBoolQuery->addShould($this->getDSubAuthorQuery($queryTerm));
                $datasetSubmissionQuery->setQuery($datasetSubmissionBoolQuery);
                $fieldsBoolQuery->addShould($datasetSubmissionQuery);
            } elseif ($specificField === self::ELASTIC_INDEX_MAPPING_THEME_KEYWORDS) {
                $datasetSubmissionBoolQuery->addShould($this->getThemeKeywordsQuery($queryTerm));
                $datasetSubmissionQuery->setQuery($datasetSubmissionBoolQuery);
                $fieldsBoolQuery->addShould($datasetSubmissionQuery);
            }

        } else {
            $fieldsBoolQuery->addShould($this->getTitleQuery($queryTerm));
            $fieldsBoolQuery->addShould($this->getAbstractQuery($queryTerm));
            $datasetSubmissionBoolQuery->addShould($this->getThemeKeywordsQuery($queryTerm));
            $datasetSubmissionBoolQuery->addShould($this->getDSubAuthorQuery($queryTerm));
            $datasetSubmissionQuery->setQuery($datasetSubmissionBoolQuery);
            $fieldsBoolQuery->addShould($datasetSubmissionQuery);
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
        $researchGroupAgg->setSize(500);

        if (!empty($requestTerms['options']['funOrgId'])) {
            $fundOrgFilter = new Aggregation\Filter('fundOrgFilter');
            $fundOrgNestedQuery = new Query\Nested();
            $fundOrgNestedQuery->setPath('researchGroup.fundingCycle.fundingOrganization');
            $fundOrgTerm = new Query\Terms();
            $fundOrgTerm->setTerms(
                'researchGroup.fundingCycle.fundingOrganization.id',
                explode(',', $requestTerms['options']['funOrgId'])
            );
            $fundOrgNestedQuery->setQuery($fundOrgTerm);
            $fundOrgFilter->setFilter($fundOrgNestedQuery);
            $fundOrgFilter->addAggregation($researchGroupAgg);

            // Add research group agg to nested
            $nestedRgAgg->addAggregation($fundOrgFilter);
        } else {
            $nestedRgAgg->addAggregation($researchGroupAgg);
        }


        // Add nested field path for funding cycle field
        $nestedFcAgg = new Aggregation\Nested('nestedFunCyc', 'researchGroup.fundingCycle');

        // Add nested field path for funding org field
        $nestedFoAgg = new Aggregation\Nested('nestedFunOrg', 'researchGroup.fundingCycle.fundingOrganization');
        // Add funding Org id field to the aggregation
        $fundingOrgAgg = new Aggregation\Terms('fundingOrgId');
        $fundingOrgAgg->setField('researchGroup.fundingCycle.fundingOrganization.id');
        $fundingOrgAgg->setSize(10);


        // Add funding Org agg to nested agg
        $nestedFoAgg->addAggregation($fundingOrgAgg);

        // Add funding org to funding cycle agg
        $nestedFcAgg->addAggregation($nestedFoAgg);
        // Add Nested fundingOrg agg to nested research group agg
        $nestedRgAgg->addAggregation($nestedFcAgg);

        return $nestedRgAgg;
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

            $rgNameQuery = new Query\Terms();
            $rgNameQuery->setTerms('researchGroup.id', explode(',', $requestTerms['options']['rgId']));
            $researchGroupNameQuery->setQuery($rgNameQuery);

            $postFilterBoolQuery->addMust($researchGroupNameQuery);
        }

        if (!empty($requestTerms['options']['funOrgId'])) {
            // Add nested field path for funding org field
            $nestedFoQuery = new Query\Nested();
            $nestedFoQuery->setPath('researchGroup.fundingCycle.fundingOrganization');

            // Add funding Org id field to the aggregation
            $fundingOrgIdQuery = new Query\Terms();
            $fundingOrgIdQuery->setTerms(
                'researchGroup.fundingCycle.fundingOrganization.id',
                explode(',', $requestTerms['options']['funOrgId'])
            );

            $nestedFoQuery->setQuery($fundingOrgIdQuery);
            $postFilterBoolQuery->addMust($nestedFoQuery);
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
     * Get the Title query.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\Match
     */
    private function getTitleQuery(string $queryTerm): Query\Match
    {
        // Add title field to the query
        $titleQuery = new Query\Match();
        $titleQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING_TITLE, $queryTerm);
        $titleQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING_TITLE, 'and');
        $titleQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING_TITLE, 2);

        return $titleQuery;
    }

    /**
     * Get the Abstract query.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\Match
     */
    private function getAbstractQuery(string $queryTerm): Query\Match
    {
        // Add abstract field to the query
        $abstractQuery = new Query\Match();
        $abstractQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING_ABSTRACT, $queryTerm);
        $abstractQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING_ABSTRACT, 'and');

        return $abstractQuery;
    }

    /**
     * Get the Theme keywords query.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\Match
     */
    private function getThemeKeywordsQuery(string $queryTerm): Query\Match
    {
        // Add theme keywords to the query
        $themeKeywordsQuery = new Query\Match();
        $themeKeywordsQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING_THEME_KEYWORDS, $queryTerm);
        $themeKeywordsQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING_THEME_KEYWORDS, 'and');
        $themeKeywordsQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING_THEME_KEYWORDS, 2);
        return $themeKeywordsQuery;
    }

    /**
     * Get the Dataset Submission Author query.
     *
     * @param string $queryTerm Query term that needs to be searched upon.
     *
     * @return Query\Match
     */
    private function getDSubAuthorQuery(string $queryTerm): Query\Match
    {
        // Add datasetSubmission author field to the query
        $authorQuery = new Query\Match();
        $authorQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING_AUTHORS, $queryTerm);
        $authorQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING_AUTHORS, 'and');
        $authorQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING_AUTHORS, 2);
        return $authorQuery;
    }
}
