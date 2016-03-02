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
     * @Route("/")
     *
     * @return Response A list of Lists.
     */
    public function listListAction()
    {
        $GLOBALS['pelagos']['title'] = 'Lists Available';
        return $this->render('PelagosAppBundle:template:UI-Lists.html.twig');
    }

    /**
     * The Research Group Generate List action.
     *
     * @Route("/ResearchGroup")
     *
     * @return Response A list of Research Groups.
     */
    public function researchGroupListAction()
    {
        $GLOBALS['pelagos']['title'] = 'Research Group List';
        return $this->render('PelagosAppBundle:template:UI-ResearchGroupList.html.twig');
    }

    /**
     * The Person Generate List action.
     *
     * @Route("/People")
     *
     * @return Response A list of People.
     */
    public function peopleListAction()
    {
        $GLOBALS['pelagos']['title'] = 'People List';
        return $this->render('PelagosAppBundle:template:UI-PersonList.html.twig');
    }

    /**
     * The Funding Organization Generate List action.
     *
     * @Route("/FundingOrganization")
     *
     * @return Response A list of People.
     */
    public function fundingOrganizationListAction()
    {
        $GLOBALS['pelagos']['title'] = 'Funding Organization List';
        return $this->render('PelagosAppBundle:template:UI-FundingOrganizationList.html.twig');
    }
}
