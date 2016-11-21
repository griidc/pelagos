<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Symfony\Component\Form\Form;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;
use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionXmlFileType;

use Pelagos\Entity\Account;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;
use Pelagos\Entity\Person;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\PersonDatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;

use Pelagos\Exception\InvalidMetadataException;

use Pelagos\Util\ISOMetadataExtractorUtil;

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
     * @throws BadRequestHttpException When xmlUploadForm is submitted without a file.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $udi = $request->query->get('regid');
        $datasetSubmission = null;
        $xmlStatus = array(
            'success' => null,
            'errors' => null,
            );

        // Assume we have a saved draft and enable discard button by default.
        $enableDiscard = true;

        if ($udi != null) {
            $udi = trim($udi);
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));

            if (count($datasets) == 1) {
                $dataset = $datasets[0];

                $dif = $dataset->getDif();

                $datasetSubmission = $dataset->getDatasetSubmissionHistory()->first();

                if (null !== $request->request->get('discard')
                    and $datasetSubmission->getStatus() === DatasetSubmission::STATUS_INCOMPLETE
                ) {
                    // Delete the current Dataset Submission.
                    $this->entityHandler->delete($datasetSubmission);
                    // Get the previous Dataset Submission (if any).
                    $datasetSubmission = $dataset->getDatasetSubmissionHistory()->first();
                }

                $xmlForm = $this->get('form.factory')->createNamed(
                    null,
                    DatasetSubmissionXmlFileType::class,
                    null
                );

                $xmlForm->handleRequest($request);

                if ($xmlForm->isSubmitted()) {
                    $xmlFile = $xmlForm['xmlFile']->getData();

                    if ($xmlFile instanceof UploadedFile) {
                        $xmlURI = $xmlFile->getRealPath();
                    } else {
                        throw new BadRequestHttpException('No file provided.');
                    }

                    try {
                        $this->loadFromXml($xmlURI, $datasetSubmission);
                        $xmlStatus['success'] = true;
                    } catch (InvalidMetadataException $e) {
                        $xmlStatus['errors'] = $e->getErrors();
                        $xmlStatus['success'] = false;
                    }
                }

                if ($datasetSubmission instanceof DatasetSubmission == false) {
                    // We didn't have a saved or submitted dataset submission, so disable discard button.
                    $enableDiscard = false;
                    // This is the first submission, so create a new one.
                    $datasetSubmission = new DatasetSubmission;

                    $datasetSubmission->setDataset($dataset);

                    $datasetSubmission->setSequence(1);

                    $datasetSubmission->setTitle($dif->getTitle());
                    $datasetSubmission->setAbstract($dif->getAbstract());

                    $datasetSubmission->setSuppParams($dif->getVariablesObserved());
                    $datasetSubmission->setSpatialExtent($dif->getSpatialExtentGeometry());
                    $datasetSubmission->setSpatialExtentDescription($dif->getSpatialExtentDescription());

                    if ($datasetSubmission->getDataset()->getMetadata() instanceof Metadata) {
                        ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                            $datasetSubmission->getDataset()->getMetadata()->getXml(),
                            $datasetSubmission,
                            $this->entityHandler
                        );
                    } else {
                        $datasetContact = new PersonDatasetSubmissionDatasetContact();
                        $datasetContact->setRole('pointOfContact');
                        $datasetContact->setPerson($dif->getPrimaryPointOfContact());
                        $datasetSubmission->addDatasetContact($datasetContact);

                        $metadataContact = new PersonDatasetSubmissionMetadataContact();
                        $metadataContact->setRole('pointOfContact');
                        $metadataContact->setPerson($dif->getPrimaryPointOfContact());
                        $datasetSubmission->addMetadataContact($metadataContact);
                    }

                    try {
                        $this->entityHandler->create($datasetSubmission);
                    } catch (AccessDeniedException $e) {
                        // This is handled in the template.
                    }
                } elseif ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
                    // We only have a submitted dataset submission, so disable discard button.
                    $enableDiscard = false;
                    // The latest submission is complete.
                    $sequence = $datasetSubmission->getSequence();
                    $datasetSubmission = clone $datasetSubmission;
                    $datasetSubmission->setSequence(++$sequence);

                    if ($datasetSubmission->getDataset()->getMetadata() instanceof Metadata
                        and $datasetSubmission->getDataset()->getMetadataStatus()
                            == DatasetSubmission::METADATA_STATUS_ACCEPTED) {
                        foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
                            $datasetSubmission->removeDatasetContact($datasetContact);
                        }

                        foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
                            $datasetSubmission->removeMetadataContact($metadataContact);
                        }

                        ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                            $datasetSubmission->getDataset()->getMetadata()->getXml(),
                            $datasetSubmission,
                            $this->entityHandler
                        );
                    } else {
                        foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
                            $datasetSubmission->removeDatasetContact($datasetContact);
                            $newDatasetContact = new PersonDatasetSubmissionDatasetContact();
                            $newDatasetContact->setRole($datasetContact->getRole());
                            $newDatasetContact->setPerson($datasetContact->getPerson());
                            $datasetSubmission->addDatasetContact($newDatasetContact);
                        }

                        foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
                            $datasetSubmission->removeMetadataContact($metadataContact);
                            $newMetadataContact = new PersonDatasetSubmissionMetadataContact();
                            $newMetadataContact->setRole($metadataContact->getRole());
                            $newMetadataContact->setPerson($metadataContact->getPerson());
                            $datasetSubmission->addMetadataContact($newMetadataContact);
                        }
                    }

                    try {
                        $this->entityHandler->create($datasetSubmission);
                    } catch (AccessDeniedException $e) {
                        // This is handled in the template.
                    }
                }
            }
        }

        return $this->makeSubmissionForm($udi, $datasetSubmission, $xmlStatus, $enableDiscard);
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

            $this->processDatasetFileTransferDetails($form, $datasetSubmission);

            $datasetSubmission->setMetadataStatus(DatasetSubmission::METADATA_STATUS_SUBMITTED);

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
     *
     * @return void
     */
    protected function processDatasetFileTransferDetails(
        Form $form,
        DatasetSubmission $datasetSubmission
    ) {
        // If there was a previous Dataset Submission.
        if ($datasetSubmission->getDataset()->getDatasetSubmission() instanceof DatasetSubmission) {
            // Get the previous datasetFileUri.
            $previousDatasetFileUri = $datasetSubmission->getDataset()->getDatasetSubmission()->getDatasetFileUri();
            // If the datasetFileUri has changed or the user has requested to force import or download.
            if ($datasetSubmission->getDatasetFileUri() !== $previousDatasetFileUri
                or $form['datasetFileForceImport']->getData()
                or $form['datasetFileForceDownload']->getData()) {
                // Assume the dataset file is new.
                $this->newDatasetFile($datasetSubmission);
            }
        } else {
            // This is the first submission so the dataset file is new.
            $this->newDatasetFile($datasetSubmission);
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
     * Make the submission form and return it.
     *
     * @param string            $udi               The UDI entered by the user.
     * @param DatasetSubmission $datasetSubmission The Dataset Submission.
     * @param array             $xmlStatus         Error message when loading XML.
     * @param boolean           $enableDiscard     Whether to enable to discard button.
     *
     * @return Response
     */
    protected function makeSubmissionForm($udi, DatasetSubmission $datasetSubmission = null, array $xmlStatus = null, $enableDiscard = true)
    {
        $datasetSubmissionId = null;
        $researchGroupId = null;
        $dataset = null;
        if ($datasetSubmission instanceof DatasetSubmission) {
            if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
            }

            if ($datasetSubmission->getMetadataContacts()->isEmpty()) {
                $datasetSubmission->addMetadataContact(new PersonDatasetSubmissionMetadataContact());
            }

            $datasetSubmissionId = $datasetSubmission->getId();
            $dataset = $datasetSubmission->getDataset();
            $researchGroupId = $dataset->getResearchGroup()->getId();
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
                    'researchGroup' => $researchGroupId,
                ),
            )
        );

        $showForceImport = false;
        $showForceDownload = false;
        if ($datasetSubmission instanceof DatasetSubmission) {
            switch ($datasetSubmission->getDatasetFileTransferType()) {
                case DatasetSubmission::TRANSFER_TYPE_SFTP:
                    $form->get('datasetFilePath')->setData(
                        preg_replace('#^file://#', '', $datasetSubmission->getDatasetFileUri())
                    );
                    if ($dataset->getDatasetSubmission() instanceof DatasetSubmission
                        and $datasetSubmission->getDatasetFileUri() == $dataset->getDatasetSubmission()->getDatasetFileUri()) {
                        $showForceImport = true;
                    }
                    break;
                case DatasetSubmission::TRANSFER_TYPE_HTTP:
                    $form->get('datasetFileUrl')->setData($datasetSubmission->getDatasetFileUri());
                    if ($dataset->getDatasetSubmission() instanceof DatasetSubmission
                        and $datasetSubmission->getDatasetFileUri() == $dataset->getDatasetSubmission()->getDatasetFileUri()) {
                        $showForceDownload = true;
                    }
                    break;
            }
        }

        $xmlFormView = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionXmlFileType::class,
            null,
            array(
                'action' => '',
                'method' => 'POST',
                'attr' => array(
                    'id' => 'xmlUploadForm',
                )
            )
        )->createView();

        $researchGroupList = null;
        if (null === $udi) {
            $account = $this->getUser();
            if (null !== $account) {
                $user = $account->getPerson();

                // Find all RG's user has CREATE_DIF_DIF_ON on.
                $researchGroups = $user->getResearchGroups();
                $researchGroupList = array_map(
                    function ($researchGroup) {
                        return $researchGroup->getId();
                    },
                    $researchGroups
                );
            }
        }

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:index.html.twig',
            array(
                'form' => $form->createView(),
                'xmlForm' => $xmlFormView,
                'udi'  => $udi,
                'xmlStatus' => $xmlStatus,
                'datasetSubmission' => $datasetSubmission,
                'showForceImport' => $showForceImport,
                'showForceDownload' => $showForceDownload,
                'researchGroupList' => $researchGroupList,
                'enableDiscard' => $enableDiscard,
            )
        );
    }

    /**
     * Load XML into Dataset from file.
     *
     * @param UploadedFile|string $xmlURI            The file containing the XML.
     * @param DatasetSubmission   $datasetSubmission The dataset submission that will be populated with XML data.
     *
     * @throws InvalidMetadataException When the file is not Simple XML.
     *
     * @return void
     */
    private function loadFromXml($xmlURI, DatasetSubmission $datasetSubmission)
    {
        $xml = simplexml_load_file($xmlURI, 'SimpleXMLElement', (LIBXML_NOERROR | LIBXML_NOWARNING));

        if ($xml instanceof \SimpleXMLElement and 'MI_Metadata' == $xml->getName()) {
            foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
                $datasetSubmission->removeDatasetContact($datasetContact);
            }
            foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
                $datasetSubmission->removeMetadataContact($metadataContact);
            }
            $accessor = PropertyAccess::createPropertyAccessor();
            $clearProperties = array(
                'title',
                'shortTitle',
                'abstract',
                'purpose',
                'suppParams',
                'suppInstruments',
                'suppMethods',
                'suppSampScalesRates',
                'suppErrorAnalysis',
                'suppProvenance',
                'referenceDate',
                'referenceDateType',
                'spatialExtent',
                'spatialExtentDescription',
                'temporalExtentDesc',
                'temporalExtentBeginPosition',
                'temporalExtentEndPosition',
                'distributionFormatName',
                'fileDecompressionTechnique',
            );
            foreach ($clearProperties as $property) {
                $accessor->setValue($datasetSubmission, $property, null);
            }
            $emptyProperties = array(
                'themeKeywords',
                'placeKeywords',
                'topicKeywords',
            );
            foreach ($emptyProperties as $property) {
                $accessor->setValue($datasetSubmission, $property, array());
            }

            ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues($xml, $datasetSubmission, $this->entityHandler);
        } else {
            throw new InvalidMetadataException(array('This does not appear to be valid ISO 19115-2 metadata.'));
        }
    }
}
