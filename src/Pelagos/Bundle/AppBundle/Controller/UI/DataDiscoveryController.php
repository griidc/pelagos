<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * The Data Discovery controller.
 *
 * @Route("/data-discovery")
 */
class DataDiscoveryController extends UIController
{
    /**
     * The default action.
     *
     * @Route("")
     * @Method("GET")
     *
     * @return Response
     */
    public function defaultAction()
    {
        return $this->render(
            'PelagosAppBundle:DataDiscovery:index.html.twig',
            array(
                'treePaneCollapsed' => false,
                'defaultFilter' => '',
                'pageName' => 'data-discovery',
                'download' => false,
            )
        );
    }

    /**
     * The datasets action.
     *
     * @Route("/datasets")
     * @Method("GET")
     *
     * @return Response
     */
    public function datasetsAction()
    {
        return $this->render(
            'PelagosAppBundle:DataDiscovery:datasets.html.twig',
            array(
                'unrestricted_datasets' => array(),
                'restricted_datasets' => array(),
                'md_under_review_datasets' => array(),
                'identified_datasets' => array(),
            )
        );
    }
}
