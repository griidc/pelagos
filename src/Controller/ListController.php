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
     *
     * @return Response A list of Lists.
     */
    #[Route(path: '/lists', name: 'pelagos_app_list_lists')]
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
     *
     * @return Response  List of Research Groups
     */
    #[Route(path: '/research-groups', name: 'pelagos_app_list_researchgroups')]
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
     *
     * @return Response  List of People
     */
    #[Route(path: '/people', name: 'pelagos_app_list_people')]
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
     *
     * @return Response  List of Funding Organizations
     */
    #[Route(path: '/funding-organizations', name: 'pelagos_app_list_fundingorganizations')]
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
     *
     * @return Response  List of Funding Organizations
     */
    #[Route(path: '/national-data-centers', name: 'pelagos_app_list_nationaldatacenters')]
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
