<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * The default controller for the Pelagos UI App Bundle.
 */
class ListController extends AbstractController
{
    /**
     * The List the Lists action.
     *
     * @Route("/lists", name="pelagos_app_list_lists")
     *
     * @return Response A list of Lists.
     */
    public function listsAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $GLOBALS['pelagos']['title'] = 'Lists Available';
        return $this->render('List/Lists.html.twig');
    }

    /**
     * The Research Group Generate List action.
     *
     * @Route("/research-groups", name="pelagos_app_list_researchgroups")
     *
     * @return Response  List of Research Groups
     */
    public function researchGroupsAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        $GLOBALS['pelagos']['title'] = 'Research Groups';
        return $this->render('List/ResearchGroups.html.twig');
    }

    /**
     * The Person Generate List action.
     *
     * @Route("/people", name="pelagos_app_list_people")
     *
     * @return Response  List of People
     */
    public function peopleAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        $GLOBALS['pelagos']['title'] = 'People';
        return $this->render('List/People.html.twig');
    }

    /**
     * The Funding Organization Generate List action.
     *
     * @Route("/funding-organizations", name="pelagos_app_list_fundingorganizations")
     *
     * @return Response  List of Funding Organizations
     */
    public function fundingOrganizationsAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        $GLOBALS['pelagos']['title'] = 'Funding Organizations';
        return $this->render('List/FundingOrganizations.html.twig');
    }

    /**
     * The National Data Repository Generate List action.
     *
     * @Route("/national-data-centers", name="pelagos_app_list_nationaldatacenters")
     *
     * @return Response  List of Funding Organizations
     */
    public function nationalDatacentersAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        $GLOBALS['pelagos']['title'] = 'National Data Centers';
        return $this->render('List/NationalDataCenters.html.twig');
    }
}
