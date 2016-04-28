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
        $udi = $request->query->get('regid');

        //var_dump($difId);
        
        $datasetSubmission = null;
        $datasetId = null;
        
        if ($udi != null) {
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => $udi));

            if (count($datasets) == 1) {
                $dataset = $datasets[0];
            }

            $datasetId = $dataset->getId();
            
            $dif = $dataset->getDif();
            
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission == false) {
                $datasetSubmission = new DatasetSubmission;
                $datasetSubmission->setTitle($dif->getTitle());
                $datasetSubmission->setAbstract($dif->getAbstract());
                $datasetSubmission->setPointOfContactName(
                $dif
                    ->getPrimaryPointOfContact()
                    ->getLastName()
                    . ', ' .
                    $dif
                    ->getPrimaryPointOfContact()
                    ->getFirstName()
                );
                $datasetSubmission->setPointOfContactEmail(
                    $dif
                    ->getPrimaryPointOfContact()
                    ->getEmailAddress()
                );
            }
        }

        if ($difId != null) {
            $dif = $this->entityHandler->get(DIF::class, $difId);
            $dataset = $dif->getDataset();
            
            $datasetId = $dataset->getId();

            $datasetSubmission = new DatasetSubmission;
            $datasetSubmission->setTitle($dif->getTitle());
            $datasetSubmission->setAbstract($dif->getAbstract());
            $datasetSubmission->setPointOfContactName(
                $dif
                ->getPrimaryPointOfContact()
                ->getLastName()
                . ', ' .
                $dif
                ->getPrimaryPointOfContact()
                ->getFirstName()
            );
            $datasetSubmission->setPointOfContactEmail(
                $dif
                ->getPrimaryPointOfContact()
                ->getEmailAddress()
            );
        }

        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $datasetSubmission,
            array(
                'action' => $this->generateUrl('pelagos_app_ui_datasetsubmission_post', array('id' => $datasetId)),
                'method' => 'POST',
            )
        );

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:index.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * The post action for Dataset Submission.
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
        $dataset = $this->entityHandler->get(Dataset::class, $id);
        
        $datasetSubmission = $dataset->getDatasetSubmission();
        if ($datasetSubmission instanceof DatasetSubmission) {
            $datasetSubmission = clone $datasetSubmission;
            $datasetSubmission->setId(null);
        } else {
             $datasetSubmission = new DatasetSubmission;
        }
        
        
        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $datasetSubmission
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataset->setDatasetSubmission($datasetSubmission);
            
            $this->entityHandler->create($datasetSubmission);
            $this->entityHandler->update($dataset);
            
            return $this->render(
                'PelagosAppBundle:DatasetSubmission:submit.html.twig',
                array('DatasetSubmission' => $datasetSubmission)
            );
        }
    }
}
