<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\DatasetSubmission;

/**
 * The Registry controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-submission")
 */
class RegistryController extends UIController
{
    /**
     * The default action for the Registry.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the Registry to load.
     *
     * @Route("/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $id = null)
    {
        $dataset = new DatasetSubmission;
        $form = $this->get('form.factory')->createNamed(null, DatasetSubmissionType::class, $dataset);

        return $this->render(
            'PelagosAppBundle:Registry:registry.html.twig',
            array('form' => $form->createView())
        );
    }
}
