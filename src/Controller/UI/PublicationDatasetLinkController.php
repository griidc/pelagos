<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * The PublicationDatasetLink controller.
 */
class PublicationDatasetLinkController extends AbstractController
{
    /**
     * The default action.
     *
     * @Route("/publink", name="pelagos_app_ui_publicationdatasetlink_default", methods={"GET"})
     *
     * @return Response A Symfony Response instance.
     */
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
     * @Route("/publink/list", name="pelagos_app_ui_publicationdatasetlink_list", methods={"GET"})
     *
     * @return Response A Symfony Response instance.
     */
    public function listAction()
    {
        return $this->render('PublicationDatasetLink/linkList.html.twig');
    }
}
