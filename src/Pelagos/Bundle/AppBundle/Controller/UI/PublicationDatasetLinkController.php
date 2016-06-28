<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\Publication;
use Pelagos\Entity\PublicationCitation;
use Pelagos\Util\PubLinkUtil;

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

}
