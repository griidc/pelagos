<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\DIF;
use Pelagos\Entity\Dataset;
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
        $difId = $request->query->get('uid');

        //var_dump($difId);

        $dataSetSubmission = new DatasetSubmission;

        if ($difId != null) {
            $dif = $this->entityHandler->get(DIF::class, $difId);
            $dataset = $dif->getDataset();

            // copy some stuff.
            $dataSetSubmission->setTitle($dif->getTitle());
            $dataSetSubmission->setAbstract($dif->getAbstract());
            $dataSetSubmission->setPointOfContactName(
                $dif
                ->getPrimaryPointOfContact()
                ->getLastName()
                . ', ' .
                $dif
                ->getPrimaryPointOfContact()
                ->getFirstName()
            );
            $dataSetSubmission->setPointOfContactEmail(
                $dif
                ->getPrimaryPointOfContact()
                ->getEmailAddress()
            );

            $dataset->setDatasetSubmission($dataSetSubmission);

            // when do this?
            //$this->entityHandler->update($dataset);
        }

        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $dataSetSubmission
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
        $dataSetSubmission = new DatasetSubmission;
        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $dataSetSubmission
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityHandler->create($dataSetSubmission);
        }

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:submit.html.twig',
            array('DatasetSubmission' => $dataSetSubmission)
        );
    }
}
