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

use Pelagos\Exception\InvalidMetadataException;

use Pelagos\Util\ISOMetadataExtractorUtil;

/**
 * The Dataset Submission controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-submission")
 */
class DatasetSubmissionController extends UIController implements OptionalReadOnlyInterface
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
        $dataset = null;
        $xmlStatus = array(
            'success' => null,
            'errors' => null,
            );

        if ($udi != null) {
            $udi = trim($udi);
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));

            if (count($datasets) == 1) {
                $dataset = $datasets[0];

                $dif = $dataset->getDif();

                $datasetSubmission = $dataset->getDatasetSubmissionHistory()->first();

                if ($datasetSubmission instanceof DatasetSubmission == false) {
                    $datasetSubmission = null;
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

                    if ($dif->getStatus() == DIF::STATUS_APPROVED) {
                        // This is the first submission, so create a new one based on the DIF.
                        $personDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact;
                        $datasetSubmission = new DatasetSubmission($dif, $personDatasetSubmissionDatasetContact);
                        $datasetSubmission->setSequence(1);

                        try {
                            $this->entityHandler->create($datasetSubmission);
                        } catch (AccessDeniedException $e) {
                            // This is handled in the template.
                        }
                    }
                } elseif ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE
                    and $datasetSubmission->getMetadataStatus() != DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER
                    and $datasetSubmission->getDataset()->getMetadata() instanceof Metadata
                ) {
                    // The latest submission is complete, so create new one based on it.
                    $datasetSubmission = new DatasetSubmission($datasetSubmission);

                    // Clear dataset and metadata contacts.
                    $datasetSubmission->getDatasetContacts()->clear();
                    // Populate from metadata.
                    ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                        $datasetSubmission->getDataset()->getMetadata()->getXml(),
                        $datasetSubmission,
                        $this->get('doctrine.orm.entity_manager')
                    );
                    // If there are no contacts, add an empty one.
                    if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                        $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
                    }
                    // Designate 1st contact as primary.
                    $primaryContact = $datasetSubmission->getDatasetContacts()->first();
                    $primaryContact->setPrimaryContact(true);

                    $submitter = $primaryContact->getPerson();
                    if (!$submitter instanceof Person) {
                        $submitter = new Person();
                    }
                    // Fake submit, without persisting.
                    $datasetSubmission->submit($submitter);

                } elseif ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE
                    and $datasetSubmission->getDatasetFileTransferStatus() !== DatasetSubmission::TRANSFER_STATUS_NONE
                    and (
                        $datasetSubmission->getDatasetFileTransferStatus() !== DatasetSubmission::TRANSFER_STATUS_COMPLETED
                        or $datasetSubmission->getDatasetFileSha256Hash() !== null
                    )
                    and $datasetSubmission->getMetadataStatus() == DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER
                ) {
                    // The latest submission is complete, so create new one based on it.
                    $datasetSubmission = new DatasetSubmission($datasetSubmission);

                    // If we have accepted metadata.
                    if ($datasetSubmission->getDataset()->getMetadata() instanceof Metadata) {

                        // Clear dataset and metadata contacts.
                        $datasetSubmission->getDatasetContacts()->clear();
                        // Populate from metadata.
                        ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                            $datasetSubmission->getDataset()->getMetadata()->getXml(),
                            $datasetSubmission,
                            $this->get('doctrine.orm.entity_manager')
                        );
                        // If there are no contacts, add an empty one.
                        if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                            $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
                        }
                        // Designate 1st contact as primary.
                        $datasetSubmission->getDatasetContacts()->first()->setPrimaryContact(true);
                    }
                    try {
                        $this->entityHandler->create($datasetSubmission);
                    } catch (AccessDeniedException $e) {
                        // This is handled in the template.
                    }
                }
            }
        }

        return $this->makeSubmissionForm($udi, $dataset, $datasetSubmission, $xmlStatus);
    }

    /**
     * The post action for Dataset Submission.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the Dataset Submission to load.
     *
     * @throws BadRequestHttpException When dataset submission has already been submitted.
     * @throws BadRequestHttpException When DIF has not yet been approved.
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

        if ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
            throw new BadRequestHttpException('This submission has already been submitted.');
        }

        if ($datasetSubmission->getDataset()->getIdentifiedStatus() != DIF::STATUS_APPROVED) {
            throw new BadRequestHttpException('The DIF has not yet been approved for this dataset.');
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

            $datasetSubmission->submit($this->getUser()->getPerson());

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
            foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
                $this->entityHandler->update($datasetContact);
            }

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
     * @param Dataset           $dataset           The Dataset.
     * @param DatasetSubmission $datasetSubmission The Dataset Submission.
     * @param array             $xmlStatus         Error message when loading XML.
     *
     * @return Response
     */
    protected function makeSubmissionForm($udi, Dataset $dataset = null, DatasetSubmission $datasetSubmission = null, array $xmlStatus = null)
    {
        $datasetSubmissionId = null;
        $researchGroupId = null;
        $datasetSubmissionStatus = null;
        if ($datasetSubmission instanceof DatasetSubmission) {
            if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
            }

            $datasetSubmissionId = $datasetSubmission->getId();
            $researchGroupId = $dataset->getResearchGroup()->getId();
            $datasetSubmissionStatus = $datasetSubmission->getStatus();
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
                    'datasetSubmissionStatus' => $datasetSubmissionStatus
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

        $researchGroupList = array();
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

        // If there are no research groups, substitute in '!*'
        // to ensure the query sent by datatables does not try and
        // search for a blank parameter.
        if (0 === count($researchGroupList)) {
            $researchGroupList = array('!*');
        }

        return $this->render(
            'PelagosAppBundle:DatasetSubmission:index.html.twig',
            array(
                'form' => $form->createView(),
                'xmlForm' => $xmlFormView,
                'udi'  => $udi,
                'xmlStatus' => $xmlStatus,
                'dataset' => $dataset,
                'datasetSubmission' => $datasetSubmission,
                'showForceImport' => $showForceImport,
                'showForceDownload' => $showForceDownload,
                'researchGroupList' => $researchGroupList,
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
            $this->clearDatasetSubmission($datasetSubmission);

            ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                $xml,
                $datasetSubmission,
                $this->get('doctrine.orm.entity_manager')
            );

            // If there are no contacts, add an empty contact.
            if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
            }
            // Designate the first contact is primary.
            $datasetSubmission->getDatasetContacts()->first()->setPrimaryContact(true);

        } else {
            throw new InvalidMetadataException(array('This does not appear to be valid ISO 19115-2 metadata.'));
        }
    }

    /**
     * Clears out data properties from a Dataset Submission.
     *
     * @param DatasetSubmission $datasetSubmission The dataset submission that will be cleared.
     *
     * @return void
     */
    private function clearDatasetSubmission(DatasetSubmission &$datasetSubmission)
    {
        $datasetSubmission->getDatasetContacts()->clear();
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
    }
}
