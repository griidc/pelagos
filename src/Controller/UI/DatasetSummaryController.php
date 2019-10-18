<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * The dataset-summary application controller.
 */
class DatasetSummaryController extends AbstractController
{
    /**
     * The default action.
     *
     * @Route(
     *      "/dataset-summary",
     *      name="pelagos_app_ui_datasetsummary_default",
     *      methods={"GET"}
     * )
     *
     * @return Response A Response instance.
     */
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        return $this->render('DatasetSummary/dataset-summary.html.twig');
    }
}
