<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * The PublicationDatasetLink controller.
 *
 * @Route("/publink")
 */
class PublicationDatasetLinkController extends UIController
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
        return $this->render('PelagosAppBundle:PublicationDatasetLink:index.html.twig');
    }

    /**
     * List all publinks.
     *
     * @Route("/list")
     * @Method("GET")
     *
     * @return Response A Symfony Response instance.
     */
    public function listAction()
    {
        return $this->render('PelagosAppBundle:PublicationDatasetLink:linkList.html.twig');
    }
}
