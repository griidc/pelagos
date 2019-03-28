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
        $researchGroupsInfo = array();
        $count = 0;
        $page = ($request->get('page')) ? $request->get('page') : 1;
        $rgId = $request->get('rgId');
        $options = array();
        if ($queryTerm) {
            if ($rgId) {
                $options = array('rgId' => $rgId);
            }
            $searchUtil = $this->get('pelagos.util.search');
            $results = $searchUtil->findDatasets($queryTerm, $page, $options);
            $count = $searchUtil->getCount($queryTerm, $options);
            $aggregations = $searchUtil->getAggregations($queryTerm, $options);
            $researchGroupsInfo = $this->getResearchGroupsInfo($aggregations);
        }

        return $this->render('PelagosAppBundle:Search:default.html.twig', array(
            'query' => $queryTerm,
            'results' => $results,
            'count' => $count,
            'page' => $page,
            'researchGroupsInfo' => $researchGroupsInfo
        ));
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
        $researchGroups = $this->entityHandler->getMultiple(ResearchGroup::class, array('id' => array_keys($aggregations)));

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
}
