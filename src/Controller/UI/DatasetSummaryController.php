<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The dataset-summary application controller.
 */
class DatasetSummaryController extends AbstractController
{
    /**
     * The default action.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/dataset-summary', name: 'pelagos_app_ui_datasetsummary_default', methods: ['GET'])]
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        return $this->render('DatasetSummary/dataset-summary.html.twig');
    }
}
