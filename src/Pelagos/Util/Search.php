<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

use Elastica\Aggregation;
use Elastica\Query;

use FOS\ElasticaBundle\Finder\TransformedFinder;

use Pagerfanta\Pagerfanta;

use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\ResearchGroup;

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
     * Elastic index mapping.
     */
    const ELASTIC_INDEX_MAPPING = array(
        'title' => 'title',
        'abstract' => 'abstract',
        'dsubAuthors' => 'datasetSubmission.authors',
        'dsubThemeKeywords' => 'datasetSubmission.themeKeywords'
    );

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

        $mainQuery = new Query();

        // Bool query to combine field query and filter query
        $subMainQuery = new Query\BoolQuery();

        // Bool query to add filters
        $filterBoolQuery = new Query\BoolQuery();

        // Bool query to add all fields
        $fieldsBoolQuery = new Query\BoolQuery();

        // Add title field to the query
        $titleQuery = new Query\Match();
        $titleQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING['title'], $queryTerm);
        $titleQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING['title'], 'and');
        $titleQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING['title'], 2);
        $fieldsBoolQuery->addShould($titleQuery);

        // Add title field to the query
        $abstractQuery = new Query\Match();
        $abstractQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING['abstract'], $queryTerm);
        $abstractQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING['abstract'], 'and');
        $fieldsBoolQuery->addShould($abstractQuery);

        // Create nested for datasetSubmission fields
        $datasetSubmissionQuery = new Query\Nested();
        $datasetSubmissionQuery->setPath('datasetSubmission');

        // Bool query to add fields in datasetSubmission
        $datasetSubmissionBoolQuery = new Query\BoolQuery();

        $themeKeywordsQuery = new Query\Match();
        $themeKeywordsQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING['dsubThemeKeywords'], $queryTerm);
        $themeKeywordsQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING['dsubThemeKeywords'], 'and');
        $themeKeywordsQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING['dsubThemeKeywords'], 2);
        $datasetSubmissionBoolQuery->addShould($themeKeywordsQuery);

        // Add datasetSubmission author field to the query
        $authorQuery = new Query\Match();
        $authorQuery->setFieldQuery(self::ELASTIC_INDEX_MAPPING['dsubAuthors'], $queryTerm);
        $authorQuery->setFieldOperator(self::ELASTIC_INDEX_MAPPING['dsubAuthors'], 'and');
        $authorQuery->setFieldBoost(self::ELASTIC_INDEX_MAPPING['dsubAuthors'], 2);
        $datasetSubmissionBoolQuery->addShould($authorQuery);

        $datasetSubmissionQuery->setQuery($datasetSubmissionBoolQuery);

        $fieldsBoolQuery->addShould($datasetSubmissionQuery);

        // Add nested field path for research group field
        $nestedRgAgg = new Aggregation\Nested('nestedResGrp', 'researchGroup');

        // Add researchGroup id field to the aggregation
        $researchGroupAgg = new Aggregation\Terms('researchGrpId');
        $researchGroupAgg->setField('researchGroup.id');
        $researchGroupAgg->setSize(500);

        // Add research group agg to nested
        $nestedRgAgg->addAggregation($researchGroupAgg);

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

        // Add nested agg to main agg
        $mainQuery->addAggregation($nestedRgAgg);

        // Add researchGroup id field to the filter
        if (!empty($requestTerms['options']['funOrgId']) || !empty($requestTerms['options']['rgId'])) {
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
                $fundingOrgIdQuery->setTerms('researchGroup.fundingCycle.fundingOrganization.id', explode(',', $requestTerms['options']['funOrgId']));

                $nestedFoQuery->setQuery($fundingOrgIdQuery);
                $postFilterBoolQuery->addMust($nestedFoQuery);
            }

            $filterBoolQuery->addMust($postFilterBoolQuery);
            $mainQuery->setParam('post_filter', $filterBoolQuery);
        }

        $subMainQuery->addMust($fieldsBoolQuery);
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

        $reseachGroupBucket = array_column(
            $userPaginator->getAdapter()->getAggregations()['nestedResGrp']['researchGrpId']['buckets'],
            'doc_count',
            'key'
        );

        return $this->getResearchGroupsInfo($reseachGroupBucket);
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
            $userPaginator->getAdapter()->getAggregations()['nestedResGrp']['nestedFunCyc']['nestedFunOrg']['fundingOrgId']['buckets'],
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
}
