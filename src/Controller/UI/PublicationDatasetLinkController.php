<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The PublicationDatasetLink controller.
 */
class PublicationDatasetLinkController extends AbstractController
{
    /**
     * The default action.
     *
     *
     * @return Response A Symfony Response instance.
     */
    #[Route(path: '/publink', name: 'pelagos_app_ui_publicationdatasetlink_default', methods: ['GET'])]
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        return $this->render('PublicationDatasetLink/index.html.twig');
    }

    /**
     * List all publinks.
     *
     *
     * @return Response A Symfony Response instance.
     */
    #[Route(path: '/publink/list', name: 'pelagos_app_ui_publicationdatasetlink_list', methods: ['GET'])]
    public function listAction()
    {
        return $this->render('PublicationDatasetLink/linkList.html.twig');
    }
}
