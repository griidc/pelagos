<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * The dataset-summary application controller.
 */
class DatasetSummaryController extends UIController
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
        return $this->render('PelagosAppBundle:DatasetSummary:dataset-summary.html.twig');
    }
}
