<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\Account;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Person;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\PersonDatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;

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
        $udi = $request->query->get('regid');
        $dataset = null;
        $datasetSubmission = null;
        $datasetSubmissionId = null;
        $dif = null;

        if ($udi != null) {
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));

            if (count($datasets) == 1) {
                $dataset = $datasets[0];

                $dif = $dataset->getDif();

                $datasetSubmission = $dataset->getDatasetSubmissionHistory()->first();

                if ($datasetSubmission instanceof DatasetSubmission == false) {
                    // This is the first submission, so create a new one.
                    $datasetSubmission = new DatasetSubmission;

                    $datasetSubmission->setDataset($dataset);

                    $datasetSubmission->setSequence(1);

                    $datasetSubmission->setTitle($dif->getTitle());
                    $datasetSubmission->setAbstract($dif->getAbstract());

                    $datasetContact = new PersonDatasetSubmissionDatasetContact();
                    $datasetContact->setDatasetSubmission($datasetSubmission);
                    $datasetContact->setRole('pointOfContact');
                    $datasetContact->setPerson($dif->getPrimaryPointOfContact());
                    $datasetSubmission->addDatasetContact($datasetContact);

                    $metadataContact = new PersonDatasetSubmissionMetadataContact();
                    $metadataContact->setDatasetSubmission($datasetSubmission);
                    $metadataContact->setRole('pointOfContact');
                    $metadataContact->setPerson($dif->getPrimaryPointOfContact());
                    $datasetSubmission->addMetadataContact($metadataContact);

                    $datasetSubmission->setSuppParams($dif->getVariablesObserved());
                    $datasetSubmission->setSpatialExtent($dif->getSpatialExtentGeometry());

                    if ($datasetSubmission->getDataset()->getMetadata() instanceof Metadata) {
                        ISOMetadataInterrogatorUtil::populateDatasetSubmissionWithXMLValues(
                            $datasetSubmission->getDataset()->getMetadata()->getXml(),
                            $datasetSubmission
                        );
                    }

                    try {
                        $this->entityHandler->create($datasetSubmission);
                    } catch (AccessDeniedException $e) {
                        // This is handled in the template.
                    }
                } elseif ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
                    // The latest submission is complete.
                    if ($datasetSubmission->getDataset()->getMetadata() instanceof Metadata) {
                        ISOMetadataInterrogatorUtil::populateDatasetSubmissionWithXMLValues(
                            $datasetSubmission->getDataset()->getMetadata()->getXml(),
                            $datasetSubmission
                        );
                    }
                    $sequence = $datasetSubmission->getSequence();
                    $datasetSubmission = clone $datasetSubmission;
                    $datasetSubmission->setSequence(++$sequence);
                    try {
                        $this->entityHandler->create($datasetSubmission);
                    } catch (AccessDeniedException $e) {
                        // This is handled in the template.
                    }
                }
            }
        }

        if ($datasetSubmission instanceof DatasetSubmission) {
            if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
            }

            if ($datasetSubmission->getMetadataContacts()->isEmpty()) {
                $datasetSubmission->addMetadataContact(new PersonDatasetSubmissionMetadataContact());
            }

            $datasetSubmissionId = $datasetSubmission->getId();
        }

        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $datasetSubmission,
            array(
                'action' => $this->generateUrl('pelagos_app_ui_datasetsubmission_post', array('id' => $datasetSubmissionId)),
                'method' => 'POST',
                'attr' => array(
                    'datasetSubmission' => $datasetSubmissionId,
                ),
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

        $form->add('submit', SubmitType::class, array(
            'label' => 'Submit',
            'attr'  => array('class' => 'submitButton'),
        ));

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:index.html.twig',
            array(
                'form' => $form->createView(),
                'udi'  => $udi,
                'datasetSubmission' => $datasetSubmission,
            )
        );
    }

    /**
     * The post action for Dataset Submission.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the Dataset Submission to load.
     *
     * @Route("/{id}")
     *
     * @Method("POST")
     *
     * @return Response A Response instance.
     */
    public function postAction(Request $request, $id = null)
    {
        $datasetSubmission = $this->entityHandler->get(DatasetSubmission::class, $id);

        if ($datasetSubmission instanceof DatasetSubmission) {
            foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
                $datasetSubmission->removeDatasetContact($datasetContact);
            }

            foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
                $datasetSubmission->removeMetadataContact($metadataContact);
            }
        }

        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $datasetSubmission
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $datasetSubmission->submit();

            if ($datasetSubmission->getSequence() > 1) {
                $eventName = 'resubmitted';
            } else {
                $eventName = 'submitted';
            }

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

            $this->entityHandler->update($datasetSubmission);

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
        // This should not normally happen.
        return new Response((string) $form->getErrors(true, false));
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
        switch ($datasetSubmission->getDatasetFileTransferType()) {
            case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                $datasetFile = $form['datasetFile']->getData();
                if ($datasetFile instanceof UploadedFile) {
                    $originalFileName = $datasetFile->getClientOriginalName();
                    $movedDatasetFile = $datasetFile->move($incomingDirectory, $originalFileName);
                    $datasetSubmission->setDatasetFileUri('file://' . $movedDatasetFile->getRealPath());
                    $this->newDatasetFile($datasetSubmission);
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_SFTP:
                $datasetFilePath = $form['datasetFilePath']->getData();
                $newDatasetFileUri = empty($datasetFilePath) ? null : "file://$datasetFilePath";
                if ($newDatasetFileUri !== $datasetSubmission->getDatasetFileUri()) {
                    $datasetSubmission->setDatasetFileUri($newDatasetFileUri);
                    $this->newDatasetFile($datasetSubmission);
                } elseif ($form['datasetFileForceImport']->getData()) {
                    $this->newDatasetFile($datasetSubmission);
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_HTTP:
                $datasetFileUrl = $form['datasetFileUrl']->getData();
                $newDatasetFileUri = empty($datasetFileUrl) ? null : $datasetFileUrl;
                if ($newDatasetFileUri !== $datasetSubmission->getDatasetFileUri()) {
                    $datasetSubmission->setDatasetFileUri($newDatasetFileUri);
                    $this->newDatasetFile($datasetSubmission);
                } elseif ($form['datasetFileForceDownload']->getData()) {
                    $this->newDatasetFile($datasetSubmission);
                }
                break;
        }
    }

    /**
     * Take appropriate actions when a new dataset file is submitted.
     *
     * @param DatasetSubmission $datasetSubmission The Dataset Submission to update.
     *
     * @return void
     */
    protected function newDatasetFile(DatasetSubmission $datasetSubmission)
    {
        $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
        $datasetSubmission->setDatasetFileName(null);
        $datasetSubmission->setDatasetFileSize(null);
        $datasetSubmission->setDatasetFileMd5Hash(null);
        $datasetSubmission->setDatasetFileSha1Hash(null);
        $datasetSubmission->setDatasetFileSha256Hash(null);
        $this->messages[] = array(
            'body' => $datasetSubmission->getDataset()->getId(),
            'routing_key' => 'dataset.' . $datasetSubmission->getDatasetFileTransferType()
        );
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
        switch ($datasetSubmission->getMetadataFileTransferType()) {
            case DatasetSubmission::TRANSFER_TYPE_UPLOAD:
                $metadataFile = $form['metadataFile']->getData();
                if ($metadataFile instanceof UploadedFile) {
                    $originalFileName = $metadataFile->getClientOriginalName();
                    $movedMetadataFile = $metadataFile->move($incomingDirectory, $originalFileName);
                    $datasetSubmission->setMetadataFileUri('file://' . $movedMetadataFile->getRealPath());
                    $this->newMetadataFile($datasetSubmission);
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_SFTP:
                $metadataFilePath = $form['metadataFilePath']->getData();
                $newMetadataFileUri = empty($metadataFilePath) ? null : "file://$metadataFilePath";
                if ($newMetadataFileUri !== $datasetSubmission->getMetadataFileUri()) {
                    $datasetSubmission->setMetadataFileUri($newMetadataFileUri);
                    $this->newMetadataFile($datasetSubmission);
                } elseif ($form['metadataFileForceImport']->getData()) {
                    $this->newMetadataFile($datasetSubmission);
                }
                break;
            case DatasetSubmission::TRANSFER_TYPE_HTTP:
                $metadataFileUrl = $form['metadataFileUrl']->getData();
                $newMetadataFileUri = empty($metadataFileUrl) ? null : $metadataFileUrl;
                if ($newMetadataFileUri !== $datasetSubmission->getMetadataFileUri()) {
                    $datasetSubmission->setMetadataFileUri($newMetadataFileUri);
                    $this->newMetadataFile($datasetSubmission);
                } elseif ($form['metadataFileForceDownload']->getData()) {
                    $this->newMetadataFile($datasetSubmission);
                }
                break;
        }
    }

    /**
     * Take appropriate actions when a new metadata file is submitted.
     *
     * @param DatasetSubmission $datasetSubmission The Dataset Submission to update.
     *
     * @return void
     */
    protected function newMetadataFile(DatasetSubmission $datasetSubmission)
    {
        $datasetSubmission->setMetadataFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
        $datasetSubmission->setMetadataFileName(null);
        $datasetSubmission->setMetadataFileSha256Hash(null);
        $this->messages[] = array(
            'body' => $datasetSubmission->getDataset()->getId(),
            'routing_key' => 'metadata.' . $datasetSubmission->getMetadataFileTransferType()
        );
    }
}
