<?php

namespace App\Controller\UI;

use App\Entity\Account;
use App\Event\LogActionItemEventDispatcher;
use App\Util\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The Search Page Controller UI.
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
     */
    public function __construct(LogActionItemEventDispatcher $logActionItemEventDispatcher, string $customTemplate)
    {
        $this->logActionItemEventDispatcher = $logActionItemEventDispatcher;
        if (empty($customTemplate)) {
            // If custom template is not set, subSite is 'GRIIDC'.
            $this->subSite = 'GRIIDC';
        } elseif ('nas-grp-base.html.twig' === $customTemplate) {
            $this->subSite = 'GRP';
        } elseif ('hri-base.html.twig' === $customTemplate) {
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
     * @Route("/search/results", name="pelagos_app_ui_searchpage_results")
     *
     * @return Response
     */
    public function getSearchResults(Request $request, Search $searchUtil)
    {
        $results = [];
        $requestParams = $this->getRequestParams($request);
        $buildQuery = $searchUtil->buildQuery($requestParams);
        $resultsBeforeHydration = $searchUtil->findDatasets($buildQuery);
        foreach ($resultsBeforeHydration as $result) {
            array_push($results, $result->getResult()->getData());
        }
        $count = $searchUtil->getCount($buildQuery);
        $researchGroupsInfo = $searchUtil->getResearchGroupAggregations($buildQuery);
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
            [
                'formValues' => [
                    'query' => $requestParams['query'],
                    'field' => $requestParams['field'],
                    'page' => $requestParams['page'],
                    'collectionStartDate' => $requestParams['collectionStartDate'],
                    'collectionEndDate' => $requestParams['collectionEndDate'],
                ],
                'resultData' => $results,
                'count' => $count,
                'facetInfo' => [
                    'researchGroupsInfo' => $researchGroupsInfo,
                    'statusInfo' => $statusInfo,
                    'fundingCycleInfo' => $fundingCycleInfo,
                    'projectDirectorInfo' => $projectDirectorInfo,
                    'funderInfo' => $funderInfo,
                ],
            ]
        );
    }

    /**
     * Gets the request parameters from the request.
     */
    private function getRequestParams(Request $request): array
    {
        return [
            'query' => $request->get('query'),
            'page' => $request->get('page'),
            'field' => $request->get('field'),
            'perPage' => $request->get('perPage'),
            'sortOrder' => $request->get('sortOrder'),
            'collectionStartDate' => $request->get('collectionStartDate'),
            'collectionEndDate' => $request->get('collectionEndDate'),
            'options' => [
                'rgId' => $request->get('researchGroup'),
                'status' => $request->get('status'),
                'fundingCycleId' => $request->get('fundingCycle'),
                'projectDirectorId' => $request->get('projectDirector'),
                'funderId' => $request->get('funder'),
            ],
            'sessionId' => $request->getSession()->getId(),
        ];
    }

    /**
     * This dispatches a search term log event.
     *
     * @param array $requestParams           the request passed from datasetAction
     * @param int   $numOfResults            number of results returned by a search
     * @param int   $elasticScoreFirstResult elastic score of the first result
     */
    protected function dispatchSearchTermsLogEvent(array $requestParams, int $numOfResults, int $elasticScoreFirstResult = null): void
    {
        // get logged in user's id
        $clientInfo = [
            'sessionId' => $requestParams['sessionId'],
        ];
        if ($this->getUser() instanceof Account) {
            $clientInfo['userType'] = 'GoMRI';
            $clientInfo['userId'] = $this->getUser()->getUserId();
        } else {
            $clientInfo['userType'] = 'Non-GoMRI';
            $clientInfo['userId'] = 'anonymous';
        }

        // get form inputs and facets
        $searchQueryParams = [
            'inputFormTerms' => [
                'searchTerms' => $requestParams['query'],
                'specificFieldType' => $requestParams['field'],
                'dataCollectionStartDate' => $requestParams['collectionStartDate'],
                'dataCollectionEndDate' => $requestParams['collectionEndDate'],
            ],
            'aggregations' => [
                'datasetStatus' => $requestParams['options']['status'],
                'funders' => $requestParams['options']['funderId'],
                'researchGroups' => $requestParams['options']['rgId'],
                'fundingCycles' => $requestParams['options']['fundingCycleId'],
                'projectDirectors' => $requestParams['options']['projectDirectorId'],
            ],
        ];

        // dispatch the event
        $this->logActionItemEventDispatcher->dispatch(
            [
                'actionName' => 'New Search',
                'subjectEntityName' => null,
                'subjectEntityId' => null,
                'payLoad' => [
                    'clientInfo' => $clientInfo,
                    'searchQueryParams' => $searchQueryParams,
                    'numResults' => $numOfResults,
                    'elasticScoreFirstResult' => $elasticScoreFirstResult,
                    'subSite' => $this->subSite,
                ],
            ],
            'search_terms_log'
        );
    }
}
