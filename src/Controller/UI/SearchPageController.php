<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Account;
use App\Event\LogActionItemEventDispatcher;
use App\Util\Search;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 */
class SearchPageController extends AbstractController
{
    /**
     * The log action item entity event dispatcher.
     *
     * @var LogActionItemEventDispatcher
     */
    protected $logActionItemEventDispatcher;

    /**
     * Subsite name, based on custom template.
     *
     * @var string
     */
    protected $subSite;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LogActionItemEventDispatcher $logActionItemEventDispatcher The log action item event dispatcher.
     */
    public function __construct(LogActionItemEventDispatcher $logActionItemEventDispatcher, string $customTemplate)
    {
        $this->logActionItemEventDispatcher = $logActionItemEventDispatcher;
        if (empty($customTemplate)) {
            // If custom template is not set, subSite is 'GRIIDC'.
            $this->subSite = 'GRIIDC';
        } elseif ($customTemplate === 'nas-grp-base.html.twig') {
            $this->subSite = 'GRP';
        } elseif ($customTemplate === 'hri-base.html.twig') {
            $this->subSite = 'HRI';
        } else {
            $this->subSite = 'UNKNOWN';
        }
    }

    /**
     * The default action for Dataset Review.
     *
     * @Route("/search", name="pelagos_app_ui_searchpage_default")
     *
     * @return Response
     */
    public function defaultAction()
    {
        return $this->render('Search/index.html.twig');
    }

    /**
     * The default action for Dataset Review.
     *
     * @param Request $request    The Symfony request object.
     * @param Search  $searchUtil Search utility class object.
     *
     * @Route("/search/results", name="pelagos_app_ui_searchpage_results")
     *
     * @return Response
     */
    public function getSearchResults(Request $request, Search $searchUtil)
    {
        $results = array();
        $requestParams = $this->getRequestParams($request);
        $buildQuery = $searchUtil->buildQuery($requestParams);
        $resultsBeforeHydration = $searchUtil->findDatasets($buildQuery);
        foreach ($resultsBeforeHydration as $result) {
            array_push($results, $result->getResult()->getData());
        }
        $count = $searchUtil->getCount($buildQuery);
        $researchGroupsInfo = $searchUtil->getResearchGroupAggregations($buildQuery);
        $fundingOrgInfo = $searchUtil->getFundingOrgAggregations($buildQuery);
        $statusInfo = $searchUtil->getStatusAggregations($buildQuery);
        $fundingCycleInfo = $searchUtil->getFundingCycleAggregations($buildQuery);
        $projectDirectorInfo = $searchUtil->getProjectDirectorAggregations($buildQuery);
        $funderInfo = $searchUtil->getFunderAggregations($buildQuery);
        $elasticScoreFirstResult = null;
        if (!empty($results)) {
            $elasticScoreFirstResult = $resultsBeforeHydration[0]->getResult()->getHit()['_score'];
        }
        $this->dispatchSearchTermsLogEvent($requestParams, $count, $elasticScoreFirstResult);

        return $this->json(
            array(
                'formValues' => array (
                    'query' => $requestParams['query'],
                    'field' => $requestParams['field'],
                    'page' => $requestParams['page'],
                    'collectionStartDate' => $requestParams['collectionStartDate'],
                    'collectionEndDate' => $requestParams['collectionEndDate'],
                ),
                'resultData' => $results,
                'count' => $count,
                'facetInfo' => array (
                    'researchGroupsInfo' => $researchGroupsInfo,
                    'fundingOrgInfo' => $fundingOrgInfo,
                    'statusInfo' => $statusInfo,
                    'fundingCycleInfo' => $fundingCycleInfo,
                    'projectDirectorInfo' => $projectDirectorInfo,
                    'funderInfo' => $funderInfo,
                ),
            )
        );
    }

    /**
     * Gets the request parameters from the request.
     *
     * @param Request $request The Symfony request object.
     *
     * @return array
     */
    private function getRequestParams(Request $request): array
    {
        return array(
            'query' => $request->get('query'),
            'page' => $request->get('page'),
            'field' => $request->get('field'),
            'perPage' => $request->get('perPage'),
            'sortOrder' => $request->get('sortOrder'),
            'collectionStartDate' => $request->get('collectionStartDate'),
            'collectionEndDate' => $request->get('collectionEndDate'),
            'options' => array(
                'rgId' => $request->get('researchGroup'),
                'funOrgId' => $request->get('fundingOrg'),
                'status' => $request->get('status'),
                'fundingCycleId' => $request->get('fundingCycle'),
                'projectDirectorId' => $request->get('projectDirector'),
                'funderId' => $request->get('funder'),
            ),
            'sessionId' => $request->getSession()->getId()
        );
    }

    /**
     * This dispatches a search term log event.
     *
     * @param array   $requestParams           The request passed from datasetAction.
     * @param integer $numOfResults            Number of results returned by a search.
     * @param integer $elasticScoreFirstResult Elastic score of the first result.
     *
     * @return void
     */
    protected function dispatchSearchTermsLogEvent(array $requestParams, int $numOfResults, int $elasticScoreFirstResult = null): void
    {
        //get logged in user's id
        $clientInfo = array(
            'sessionId' => $requestParams['sessionId']
        );
        if ($this->getUser() instanceof Account) {
            $clientInfo['userType'] = 'GoMRI';
            $clientInfo['userId'] = $this->getUser()->getUserId();
        } else {
            $clientInfo['userType'] = 'Non-GoMRI';
            $clientInfo['userId'] = 'anonymous';
        }

        //get form inputs and facets
        $searchQueryParams = array(
            'inputFormTerms' => array(
                'searchTerms' => $requestParams['query'] ,
                'specificFieldType' => $requestParams['field'],
                'dataCollectionStartDate' => $requestParams['collectionStartDate'],
                'dataCollectionEndDate' => $requestParams['collectionEndDate'],
            ),
            'aggregations' => array(
                'datasetStatus' => $requestParams['options']['status'],
                'fundingOrganizations' => $requestParams['options']['funOrgId'],
                'researchGroups' => $requestParams['options']['rgId'],
                'fundingCycles' => $requestParams['options']['fundingCycleId'],
                'projectDirectors' => $requestParams['options']['projectDirectorId']
            )
        );

        //dispatch the event
        $this->logActionItemEventDispatcher->dispatch(
            array(
                'actionName' => 'New Search',
                'subjectEntityName' => null,
                'subjectEntityId' => null,
                'payLoad' => array(
                    'clientInfo' => $clientInfo,
                    'searchQueryParams' => $searchQueryParams,
                    'numResults' => $numOfResults,
                    'elasticScoreFirstResult' => $elasticScoreFirstResult,
                    'subSite' => $this->subSite
                )
            ),
            'search_terms_log'
        );
    }
}
