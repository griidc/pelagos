<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * The default controller for the Pelagos UI App Bundle.
 */
class ListController extends Controller
{
    /**
     * The List the Lists action.
     *
     * @Route("/lists")
     *
     * @return Response A list of Lists.
     */
    public function listsAction()
    {
        $GLOBALS['pelagos']['title'] = 'Lists Available';
        return $this->render('PelagosAppBundle:List:Lists.html.twig');
    }

    /**
     * The Research Group Generate List action.
     *
     * @Route("/research-groups")
     *
     * @return Response A list of Research Groups.
     */
    public function researchGroupsAction()
    {
        $GLOBALS['pelagos']['title'] = 'Research Groups';
        return $this->render('PelagosAppBundle:List:ResearchGroups.html.twig');
    }

    /**
     * The Person Generate List action.
     *
     * @Route("/people")
     *
     * @return Response A list of People.
     */
    public function peopleAction()
    {
        $GLOBALS['pelagos']['title'] = 'People';
        return $this->render('PelagosAppBundle:List:People.html.twig');
    }

    /**
     * The Funding Organization Generate List action.
     *
     * @Route("/funding-organizations")
     *
     * @return Response A list of People.
     */
    public function fundingOrganizationsAction()
    {
        $GLOBALS['pelagos']['title'] = 'Funding Organizations';
        return $this->render('PelagosAppBundle:List:FundingOrganizations.html.twig');
    }
}
