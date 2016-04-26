<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\DatasetSubmission;

/**
 * The Dataset Submission controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-submission")
 */
class DatasetSubmissionController extends UIController
{
    /**
     * The default action for Dataset Submission.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the Dataset to load.
     *
     * @Route("/{id}")
     *
     * @Method("GET")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $id = null)
    {
        $dataset = new DatasetSubmission;
        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $dataset
        );

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:index.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * The default action for Dataset Submission.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the Dataset to load.
     *
     * @Route("/{id}")
     *
     * @Method("POST")
     *
     * @return Response A Response instance.
     */
    public function postAction(Request $request, $id = null)
    {
        $dataset = new DatasetSubmission;
        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $dataset
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ... perform some action, such as saving the task to the database

            echo 'works!';

            $this->container->get('pelagos.entity.handler')->create($dataset);
        }

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:index.html.twig',
            array('form' => $form->createView())
        );
    }
}
