<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\DIFType;

use Pelagos\Entity\DIF;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 *
 * @Route("/dif")
 */
class DIFController extends UIController
{
    /**
     * The default action for the DIF.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the DIF to load.
     *
     * @Route("/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $id = null)
    {
        $dif = new DIF;
        $form = $this->get('form.factory')->createNamed(null, DIFType::class, $dif);

        return $this->render(
            'PelagosAppBundle:DIF:difForm.html.twig',
            array('form' => $form->createView())
        );
    }
}
