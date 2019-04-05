<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/search")
 */
class SearchPageController extends UIController
{

    /**
     * The default action for Dataset Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     * @return Response
     */
    public function defaultAction(Request $request)
    {
        $results = array();
        $count = 0;
        $requestParams = $this->getRequestParams($request);
        $researchGroupsInfo = array();
        $fundingOrgInfo = array();

        if (!empty($requestParams['query'])) {
            $searchUtil = $this->get('pelagos.util.search');
            $buildQuery = $searchUtil->buildQuery($requestParams);
            $results = $searchUtil->findDatasets($buildQuery);
            $count = $searchUtil->getCount($buildQuery);
            $researchGroupsInfo = $searchUtil->getResearchGroupAggregations($buildQuery);
            $fundingOrgInfo = $searchUtil->getFundingOrgAggregations($buildQuery);
        }

        return $this->render('PelagosAppBundle:Search:default.html.twig', array(
            'query' => $requestParams['query'],
            'results' => $results,
            'count' => $count,
            'page' => $requestParams['page'],
            'researchGroupsInfo' => $researchGroupsInfo,
            'fundingOrgInfo' => $fundingOrgInfo
        ));
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
            'options' => array(
                'rgId' => $request->get('rgId')
            )
        );
    }
}
