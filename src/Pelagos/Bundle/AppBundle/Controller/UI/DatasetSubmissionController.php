<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\Account;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Person;
use Pelagos\Entity\ResearchGroup;

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
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     * @Method("GET")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $difId = $request->query->get('uid');
        $udi = $request->query->get('regid');

        $datasetSubmission = null;
        $datasetId = null;

        $found = false;

        $buttonLabel = 'Register';

        if ($udi != null) {
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));

            if (count($datasets) == 1) {
                $dataset = $datasets[0];

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
                } else {
                    $buttonLabel = 'Update';
                }
                $found = true;
            }
        }

        if ($difId != null) {
            $dif = $this->entityHandler->get(DIF::class, $difId);

            if ($dif instanceof DIF) {
                $dataset = $dif->getDataset();

                $datasetId = $dataset->getId();

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
                } else {
                    $udi = $dataset->getUdi();
                }
                $found = true;
            }
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

        if ($datasetSubmission instanceof DatasetSubmission) {
            switch ($datasetSubmission->getDatasetFileTransferType()) {
                case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                    $form->get('datasetFileUpload')->setData(
                        preg_replace('#^file://#', '', $datasetSubmission->getDatasetFileUri())
                    );
                    break;
                case DatasetSubmission::TRANSFER_TYPE_SFTP:
                    $form->get('datasetFilePath')->setData(
                        preg_replace('#^file://#', '', $datasetSubmission->getDatasetFileUri())
                    );
                    break;
                case DatasetSubmission::TRANSFER_TYPE_HTTP:
                    $form->get('datasetFileUrl')->setData($datasetSubmission->getDatasetFileUri());
                    break;
            }

            switch ($datasetSubmission->getMetadataFileTransferType()) {
                case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                    $form->get('metadataFileUpload')->setData(
                        preg_replace('#^file://#', '', $datasetSubmission->getMetadataFileUri())
                    );
                    break;
                case DatasetSubmission::TRANSFER_TYPE_SFTP:
                    $form->get('metadataFilePath')->setData(
                        preg_replace('#^file://#', '', $datasetSubmission->getMetadataFileUri())
                    );
                    break;
                case DatasetSubmission::TRANSFER_TYPE_HTTP:
                    $form->get('metadataFileUrl')->setData($datasetSubmission->getMetadataFileUri());
                    break;
            }
        }

        if ($this->getUser() instanceof Account) {
            $loggedInPerson = $this->getUser()->getPerson();
        } else {
            $loggedInPerson = null;
        }

        $form->add('submit', SubmitType::class, array(
            'label' => $buttonLabel,
            'attr'  => array('class' => 'submitButton'),
        ));

        $datasetSubmissions = $this->entityHandler
            ->getBy(
                Dataset::class,
                array (
                    'datasetSubmission.creator' => $loggedInPerson,
                )
            );

        $researchGroups = array();
        foreach ($this->entityHandler->getAll(ResearchGroup::class) as $entity) {
            if ($this->isGranted('CAN_CREATE_DIF_FOR', $entity)) {
                $researchGroups[] = $entity;
            }
        }

        $researchers = $this->entityHandler
            ->getAll(Person::class);

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:index.html.twig',
            array(
                'form' => $form->createView(),
                'datasetSubmission' => $datasetSubmission,
                'found'  => $found,
                'udi'  => $udi,
                'datasetSubmissions' => $datasetSubmissions,
                'researchGroups' => $researchGroups,
                'researchers' => $researchers,
                'loggedInPerson' => $loggedInPerson,
            )
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
            $sequence = $datasetSubmission->getSequence();
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
            $sequence = $datasetSubmission->getSequence();

            if ($sequence == null) {
                $sequence = 0;
                $eventName = 'submitted';
            } else {
                $eventName = 'resubmitted';
            }

            $datasetSubmission->setSequence(++$sequence);

            if ($this->getUser()->isPosix()) {
                $incomingDirectory = $this->getUser()->getHomeDirectory() . '/incoming';
            } else {
                $incomingDirectory = $this->getParameter('homedir_prefix') . '/upload/'
                                         . $this->getUser()->getUserName() . '/incoming';
                if (!file_exists($incomingDirectory)) {
                    mkdir($incomingDirectory, 0755, true);
                }
            }

            switch ($datasetSubmission->getDatasetFileTransferType()) {
                case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                    $datasetFile = $form['datasetFile']->getData();
                    if ($datasetFile instanceof UploadedFile) {
                        $originalFileName = $datasetFile->getClientOriginalName();
                        $movedDatasetFile = $datasetFile->move($incomingDirectory, $originalFileName);
                        $datasetSubmission->setDatasetFileUri('file://' . $movedDatasetFile->getRealPath());
                    }
                    break;
                case DatasetSubmission::TRANSFER_TYPE_SFTP:
                    $datasetFilePath = $form['datasetFilePath']->getData();
                    if (null === $datasetFilePath) {
                        $datasetSubmission->setDatasetFileUri(null);
                    } else {
                        $datasetSubmission->setDatasetFileUri("file://$datasetFilePath");
                    }
                    break;
                case DatasetSubmission::TRANSFER_TYPE_HTTP:
                    $datasetSubmission->setDatasetFileUri($form['datasetFileUrl']->getData());
                    break;
            }

            switch ($datasetSubmission->getMetadataFileTransferType()) {
                case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                    $metadataFile = $form['metadataFile']->getData();
                    if ($metadataFile instanceof UploadedFile) {
                        $originalFileName = $metadataFile->getClientOriginalName();
                        $movedMetadataFile = $metadataFile->move($incomingDirectory, $originalFileName);
                        $datasetSubmission->setMetadataFileUri('file://' . $movedMetadataFile->getRealPath());
                    }
                    break;
                case DatasetSubmission::TRANSFER_TYPE_SFTP:
                    $metadataFilePath = $form['metadataFilePath']->getData();
                    if (null === $metadataFilePath) {
                        $datasetSubmission->setMetadataFileUri(null);
                    } else {
                        $datasetSubmission->setMetadataFileUri("file://$metadataFilePath");
                    }
                    break;
                case DatasetSubmission::TRANSFER_TYPE_HTTP:
                    $datasetSubmission->setMetadataFileUri($form['metadataFileUrl']->getData());
                    break;
            }

            $this->entityHandler->create($datasetSubmission);
            $this->entityHandler->update($dataset);

            $this->container->get('pelagos.entity.handler')->dispatchEntityEvent(
                $datasetSubmission,
                $eventName
            );

            return $this->render(
                'PelagosAppBundle:DatasetSubmission:submit.html.twig',
                array('DatasetSubmission' => $datasetSubmission)
            );
        }
    }
}
