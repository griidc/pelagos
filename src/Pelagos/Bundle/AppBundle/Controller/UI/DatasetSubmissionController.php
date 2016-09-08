<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Form;
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
     * A queue of messages to publish to RabbitMQ.
     *
     * @var array
     */
    protected $messages = array();

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

        $dif = null;

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

        if ($dif instanceof DIF and $dif->getStatus() !== DIF::STATUS_APPROVED) {
            $datasetSubmission = null;
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
                'dif' => $dif,
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
            $datasetSubmission->setCreationTimeStamp(null);
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

            $this->processDatasetFileTransferDetails($form, $datasetSubmission, $incomingDirectory);

            $this->processMetadataFileTransferDetails($form, $datasetSubmission, $incomingDirectory);

            $this->entityHandler->create($datasetSubmission);
            $this->entityHandler->update($dataset);

            $this->container->get('pelagos.event.entity_event_dispatcher')->dispatch(
                $datasetSubmission,
                $eventName
            );

            foreach ($this->messages as $message) {
                $this->get('old_sound_rabbit_mq.dataset_submission_producer')->publish(
                    $message['body'],
                    $message['routing_key']
                );
            }

            return $this->render(
                'PelagosAppBundle:DatasetSubmission:submit.html.twig',
                array('DatasetSubmission' => $datasetSubmission)
            );
        }
    }

    /**
     * Process the Dataset File Transfer Details and update the Dataset Submission.
     *
     * @param Form              $form              The submitted dataset submission form.
     * @param DatasetSubmission $datasetSubmission The Dataset Submission to update.
     * @param string            $incomingDirectory The user's incoming directory.
     *
     * @return void
     */
    protected function processDatasetFileTransferDetails(
        Form $form,
        DatasetSubmission $datasetSubmission,
        $incomingDirectory
    ) {
        $datasetId = $datasetSubmission->getDataset()->getId();
        switch ($datasetSubmission->getDatasetFileTransferType()) {
            case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                $datasetFile = $form['datasetFile']->getData();
                if ($datasetFile instanceof UploadedFile) {
                    $originalFileName = $datasetFile->getClientOriginalName();
                    $movedDatasetFile = $datasetFile->move($incomingDirectory, $originalFileName);
                    $datasetSubmission->setDatasetFileUri('file://' . $movedDatasetFile->getRealPath());
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'dataset.upload');
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_SFTP:
                $datasetFilePath = $form['datasetFilePath']->getData();
                $newDatasetFileUri = empty($datasetFilePath) ? null : "file://$datasetFilePath";
                if ($newDatasetFileUri !== $datasetSubmission->getDatasetFileUri()) {
                    $datasetSubmission->setDatasetFileUri($newDatasetFileUri);
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'dataset.SFTP');
                } elseif ($form['datasetFileForceImport']->getData()) {
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'dataset.SFTP');
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_HTTP:
                $datasetFileUrl = $form['datasetFileUrl']->getData();
                $newDatasetFileUri = empty($datasetFileUrl) ? null : $datasetFileUrl;
                if ($newDatasetFileUri !== $datasetSubmission->getDatasetFileUri()) {
                    $datasetSubmission->setDatasetFileUri($newDatasetFileUri);
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'dataset.HTTP');
                } elseif ($form['datasetFileForceDownload']->getData()) {
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'dataset.HTTP');
                }
                break;
        }
    }

    /**
     * Process the Metadata File Transfer Details and update the Dataset Submission.
     *
     * @param Form              $form              The submitted dataset submission form.
     * @param DatasetSubmission $datasetSubmission The Dataset Submission to update.
     * @param string            $incomingDirectory The user's incoming directory.
     *
     * @return void
     */
    protected function processMetadataFileTransferDetails(
        Form $form,
        DatasetSubmission $datasetSubmission,
        $incomingDirectory
    ) {
        $datasetId = $datasetSubmission->getDataset()->getId();
        switch ($datasetSubmission->getMetadataFileTransferType()) {
            case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                $metadataFile = $form['metadataFile']->getData();
                if ($metadataFile instanceof UploadedFile) {
                    $originalFileName = $metadataFile->getClientOriginalName();
                    $movedMetadataFile = $metadataFile->move($incomingDirectory, $originalFileName);
                    $datasetSubmission->setMetadataFileUri('file://' . $movedMetadataFile->getRealPath());
                    $datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'metadata.upload');
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_SFTP:
                $metadataFilePath = $form['metadataFilePath']->getData();
                $newMetadataFileUri = empty($metadataFilePath) ? null : "file://$metadataFilePath";
                if ($newMetadataFileUri !== $datasetSubmission->getMetadataFileUri()) {
                    $datasetSubmission->setMetadataFileUri($newMetadataFileUri);
                    $datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'metadata.SFTP');
                } elseif ($form['metadataFileForceImport']->getData()) {
                    $datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'metadata.SFTP');
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_HTTP:
                $metadataFileUrl = $form['metadataFileUrl']->getData();
                $newMetadataFileUri = empty($metadataFileUrl) ? null : $metadataFileUrl;
                if ($newMetadataFileUri !== $datasetSubmission->getMetadataFileUri()) {
                    $datasetSubmission->setMetadataFileUri($newMetadataFileUri);
                    $datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'metadata.HTTP');
                } elseif ($form['metadataFileForceDownload']->getData()) {
                    $datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $this->messages[] = array('body' => $datasetId, 'routing_key' => 'metadata.HTTP');
                }
                break;
        }
    }
}
