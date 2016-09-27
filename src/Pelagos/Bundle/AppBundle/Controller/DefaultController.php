<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * The default controller for the Pelagos App Bundle.
 */
class DefaultController extends Controller
{
    /**
     * The index action.
     *
     * @return Response A Response instance.
     */
    public function indexAction()
    {
        return $this->render('PelagosAppBundle:Default:index.html.twig');
    }

    /**
     * The admin action.
     *
     * @return Response A Response instance.
     */
    public function adminAction()
    {
        return $this->render('PelagosAppBundle:Default:admin.html.twig');
    }

    /**
     * The dataset summary action.
     *
     * @return Response A Response instance.
     */
    public function datasetSummaryAction()
    {
        return $this->render('PelagosAppBundle:Default:dataset-summary.html.twig');
    }
}
