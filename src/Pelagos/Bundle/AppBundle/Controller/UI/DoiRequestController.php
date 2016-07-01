<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The DOI Request controller for the Pelagos UI App Bundle.
 *
 * @Route("/doi-request")
 */
class DoiRequestController extends UIController
{
    /**
     * The default action for the DOI Request.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->render('PelagosAppBundle:DIF:notLoggedIn.html.twig');
        }

        // $dif = new DIF;
        // $form = $this->get('form.factory')->createNamed(null, DIFType::class, $dif);

        return $this->render(
            'PelagosAppBundle:DoiRequest:index.html.twig',
            array()
        );
    }
}
