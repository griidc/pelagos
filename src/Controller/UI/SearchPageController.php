<?php

namespace App\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Util\Search;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 */
class SearchPageController extends AbstractController
{

    /**
     * The default action for Dataset Review.
     *
     * @param Request $request    The Symfony request object.
     * @param Search  $searchUtil Search utility class object.
     *
     * @Route("/search", name="pelagos_app_ui_searchpage_default")
     *
     * @return Response
     */
    public function defaultAction(Request $request, Search $searchUtil)
    {
        if ($this->getParameter('kernel.debug')) {
            $results = array();
            $count = 0;
            $requestParams = $this->getRequestParams($request);
            $researchGroupsInfo = array();
            $fundingOrgInfo = array();
            $statusInfo = array();

            if (!empty($requestParams['query'])) {
                $buildQuery = $searchUtil->buildQuery($requestParams);
                $results = $searchUtil->findDatasets($buildQuery);
                $count = $searchUtil->getCount($buildQuery);
                $researchGroupsInfo = $searchUtil->getResearchGroupAggregations($buildQuery);
                $fundingOrgInfo = $searchUtil->getFundingOrgAggregations($buildQuery);
                $statusInfo = $searchUtil->getStatusAggregations($buildQuery);
            }

            return $this->render(
                'Search/default.html.twig',
                array(
                    'query' => $requestParams['query'],
                    'field' => $requestParams['field'],
                    'results' => $results,
                    'count' => $count,
                    'page' => $requestParams['page'],
                    'researchGroupsInfo' => $researchGroupsInfo,
                    'fundingOrgInfo' => $fundingOrgInfo,
                    'statusInfo' => $statusInfo,
                    'collectionStartDate' => $requestParams['collectionStartDate'],
                    'collectionEndDate' => $requestParams['collectionEndDate'],
                )
            );
        }

        return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
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
            'collectionStartDate' => $request->get('collectionStartDate'),
            'collectionEndDate' => $request->get('collectionEndDate'),
            'options' => array(
                'rgId' => ($request->get('resGrp')) ? str_replace('rg_', '', $request->get('resGrp')) : null,
                'funOrgId' => ($request->get('fundOrg')) ? str_replace('fo_', '', $request->get('fundOrg')) : null,
                'status' => $request->get('status') ? str_replace('status_', '', $request->get('status')) : null,
            )
        );
    }

    /**
     * @Route("/search-react-app/{reactRouting}", name="reach_search_app", defaults={"reactRouting": null})
     */
    public function index()
    {
        return $this->render('Search/base.html.twig');
    }
}
