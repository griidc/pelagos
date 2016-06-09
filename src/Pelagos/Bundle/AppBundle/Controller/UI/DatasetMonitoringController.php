<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * The Dataset Monitoring controller.
 *
 * @Route("/dataset-monitoring")
 */
class DatasetMonitoringController extends UIController
{
    /**
     * The default action.
     *
     * @Route("")
     * @Method("GET")
     *
     * @return Response A Symfony Response instance.
     */
    public function defaultAction()
    {
        return $this->render('PelagosAppBundle:DatasetMonitoring:index.html.twig');
    }
}
