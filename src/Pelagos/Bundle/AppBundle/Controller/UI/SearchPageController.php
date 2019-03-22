<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Entity\ResearchGroup;

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
        $queryTerm = $request->get('query');
        $results = array();
        $count = 0;
        $page = ($request->get('page')) ? $request->get('page') : 1;

        if ($queryTerm) {
            $searchUtil = $this->get('pelagos.util.search');
            $results = $searchUtil->findDatasets($queryTerm, $page);
            $count = $searchUtil->getCount($queryTerm);
//            $aggregations = $searchUtil->getAggregations($queryTerm);
//            $this->getResearchGroupNames($aggregations);
//            dump($aggregations);
//            exit;
        }

        return $this->render('PelagosAppBundle:Search:default.html.twig', array(
            'query' => $queryTerm,
            'results' => $results,
            'count' => $count,
            'page' => $page
        ));
    }

    private function getResearchGroupNames(array $aggregations)
    {

        $researchGroups = $this->entityHandler->getMultiple(ResearchGroup::class, array('id' => array_keys($aggregations)));

        dump($researchGroups);
        exit;
    }
}
