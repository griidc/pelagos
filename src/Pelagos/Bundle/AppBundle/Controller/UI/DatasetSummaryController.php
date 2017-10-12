<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * The dataset-summary application controller.
 */
class DatasetSummaryController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action.
     *
     * @Route("/dataset-summary")
     *
     * @return Response A Response instance.
     */
    public function defaultAction()
    {
        // Added authorization check for users to view this page
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        return $this->render('PelagosAppBundle:DatasetSummary:dataset-summary.html.twig');
    }
}
