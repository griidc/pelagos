<?php

namespace App\Controller\UI;

use App\Entity\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Form;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\DatasetSubmissionType;
use App\Form\DatasetSubmissionXmlFileType;
use App\Event\EntityEventDispatcher;
use App\Entity\DataCenter;
use App\Entity\DIF;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DistributionPoint;
use App\Entity\Fileset;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use App\Entity\PersonDatasetSubmissionMetadataContact;
use App\Handler\EntityHandler;
use App\Exception\InvalidMetadataException;
use App\Message\DatasetSubmissionFiler;
use App\Util\ISOMetadataExtractorUtil;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * The Dataset Submission controller for the Pelagos UI App Bundle.
 */
class DatasetSubmissionController extends AbstractController
{
    /**
     * A queue of messages to publish to RabbitMQ.
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Name of the default distribution contact.
     */
    const DEFAULT_DISTRIBUTION_POINT_CONTACT_EMAIL = 'griidc@gomri.org';

    /**
     * Name of the default distribution contact.
     */
    const DEFAULT_DISTRIBUTION_POINT_ROLECODE = 'distributor';

    /**
     * Protected entityHandler value instance of entityHandler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * Protected entity event dispatcher.
     *
     * @var EntityEventDispatcher
     */
    protected $entityEventDispatcher;

    /**
     * The Form Factory.
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityHandler         $entityHandler         The entity handler.
     * @param EntityEventDispatcher $entityEventDispatcher The entity event dispatcher.
     */
    public function __construct(EntityHandler $entityHandler, EntityEventDispatcher $entityEventDispatcher, FormFactoryInterface $formFactory)
    {
        $this->entityHandler = $entityHandler;
        $this->entityEventDispatcher = $entityEventDispatcher;
        $this->formFactory = $formFactory;
    }

    /**
     * The default action for Dataset Submission.
     *
     * @param Request                $request       The Symfony request object.
     * @param EntityManagerInterface $entityManager A Pelagos EntityManager.
     *
     * @throws BadRequestHttpException When xmlUploadForm is submitted without a file.
     *
     * @Route("/dataset-submission", name="pelagos_app_ui_datasetsubmission_default", methods={"GET", "POST"})
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, EntityManagerInterface $entityManager)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect(
                $this->generateUrl('security_login') . '?destination='
                . $this->generateUrl('pelagos_app_ui_datasetsubmission_default')
            );
        }

        $udi = $request->query->get('regid');
        $datasetSubmission = null;
        $dataset = null;
        $xmlStatus = array(
            'success' => null,
            'errors' => null,
            );
        $createFlag = false;

        if ($udi != null) {
            $udi = trim($udi);
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));

            if (count($datasets) == 1) {
                $dataset = $datasets[0];

                $dif = $dataset->getDif();

                $datasetSubmission = $this->getDatasetSubmission($dataset);

                $xmlForm = $this->formFactory->createNamed('', DatasetSubmissionXmlFileType::class);

                $xmlForm->handleRequest($request);

                if ($xmlForm->isSubmitted()) {
                    $xmlFile = $xmlForm['xmlFile']->getData();

                    if ($xmlFile instanceof UploadedFile) {
                        $xmlURI = $xmlFile->getRealPath();
                    } else {
                        throw new BadRequestHttpException('No file provided.');
                    }

                    try {
                        $this->loadFromXml($xmlURI, $datasetSubmission, $entityManager);
                        $xmlStatus['success'] = true;
                    } catch (InvalidMetadataException $e) {
                        $xmlStatus['errors'] = $e->getErrors();
                        $xmlStatus['success'] = false;
                    }
                }

                if ($datasetSubmission instanceof DatasetSubmission == false) {
                    if ($dif->getStatus() == DIF::STATUS_APPROVED) {
                        // This is the first submission, so create a new one based on the DIF.
                        $personDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact();
                        $datasetSubmission = new DatasetSubmission($dif, $personDatasetSubmissionDatasetContact);
                        $datasetSubmission->setSequence(1);

                        $createFlag = true;
                    }
                } elseif (
                    $datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE
                    and $dataset->getDatasetStatus() === Dataset::DATASET_STATUS_BACK_TO_SUBMITTER
                ) {
                    // The latest submission is complete, so create new one based on it.
                    $datasetSubmission = new DatasetSubmission($datasetSubmission);
                    $datasetSubmission->setDatasetStatus(Dataset::DATASET_STATUS_BACK_TO_SUBMITTER);
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $createFlag = true;
                }

                if ($createFlag) {
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
     * @param integer|null $id The id of the Dataset Submission to load.
     *
     * @throws BadRequestHttpException When dataset submission has already been submitted.
     * @throws BadRequestHttpException When DIF has not yet been approved.
     *
     * @Route("/dataset-submission/{id}", name="pelagos_app_ui_datasetsubmission_post", methods={"POST"})
     *
     * @return Response A Response instance
     */
    public function postAction(Request $request, MessageBusInterface $messageBus, EntityManagerInterface $entityManager, int $id = null)
    {
        $datasetSubmission = $entityManager->getRepository(DatasetSubmission::class)->find($id);

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect(
                $this->generateUrl('security_login') . '?destination='
                . $this->generateUrl('pelagos_app_ui_datasetsubmission_default') . '?regid='
                . $datasetSubmission->getDataset()->getUdi()
            );
        }

        if ($datasetSubmission->getStatus() === DatasetSubmission::STATUS_COMPLETE) {
            throw new BadRequestHttpException('This submission has already been submitted.');
        }

        if ($datasetSubmission->getDataset()->getIdentifiedStatus() != DIF::STATUS_APPROVED) {
            throw new BadRequestHttpException('The DIF has not yet been approved for this dataset.');
        }

        $form = $this->formFactory->createNamed(
            '',
            DatasetSubmissionType::class,
            $datasetSubmission
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $datasetSubmission->setDatasetStatus(Dataset::DATASET_STATUS_SUBMITTED);

            $datasetSubmission->submit($this->getUser()->getPerson());

            if ($datasetSubmission->getSequence() > 1) {
                $eventName = 'resubmitted';
            } else {
                $eventName = 'submitted';
            }

            $fileset = $datasetSubmission->getFileset();
            if ($fileset instanceof Fileset) {
                foreach ($fileset->getNewFiles() as $file) {
                    $file->setStatus(File::FILE_IN_QUEUE);
                }
            }

            $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_BEING_PROCESSED);

            $entityManager->flush();

            $this->entityEventDispatcher->dispatch(
                $datasetSubmission,
                $eventName
            );

            $datasetSubmissionFilerMessage = new DatasetSubmissionFiler($datasetSubmission->getId());
            $messageBus->dispatch($datasetSubmissionFilerMessage);

            return $this->render(
                'DatasetSubmission/submit.html.twig',
                array('DatasetSubmission' => $datasetSubmission)
            );
        }
        // This should not normally happen.
        return new Response((string) $form->getErrors(true, false));
    }

    /**
     * Make the submission form and return it.
     *
     * @param string|null       $udi               The UDI entered by the user.
     * @param Dataset           $dataset           The Dataset.
     * @param DatasetSubmission $datasetSubmission The Dataset Submission.
     * @param array             $xmlStatus         Error message when loading XML.
     *
     * @return Response
     */
    protected function makeSubmissionForm(?string $udi, Dataset $dataset = null, DatasetSubmission $datasetSubmission = null, array $xmlStatus = null)
    {
        $datasetSubmissionId = null;
        $researchGroupId = null;
        $datasetSubmissionLockStatus = null;
        if ($datasetSubmission instanceof DatasetSubmission) {
            if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
            }

            if ($datasetSubmission->getMetadataContacts()->isEmpty()) {
                $datasetSubmission->addMetadataContact(new PersonDatasetSubmissionMetadataContact());
            }

            $datasetSubmission = $this->defaultDistributionPoint($datasetSubmission, $udi);

            $datasetSubmissionId = $datasetSubmission->getId();
            $researchGroupId = $dataset->getResearchGroup()->getId();
            $datasetSubmissionLockStatus = $this->isSubmissionLocked($dataset);
        }

        $form = $this->formFactory->createNamed(
            '',
            DatasetSubmissionType::class,
            $datasetSubmission,
            array(
                'action' => $this->generateUrl('pelagos_app_ui_datasetsubmission_post', array('id' => $datasetSubmissionId)),
                'method' => 'POST',
                'attr' => array(
                    'datasetSubmission' => $datasetSubmissionId,
                    'researchGroup' => $researchGroupId,
                    'datasetSubmissionStatus' => (int) $datasetSubmissionLockStatus
                ),
            )
        );

        $xmlFormView = $this->formFactory->createNamed(
            '',
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
            'DatasetSubmission/index.html.twig',
            array(
                'form' => $form->createView(),
                'xmlForm' => $xmlFormView,
                'udi'  => $udi,
                'xmlStatus' => $xmlStatus,
                'dataset' => $dataset,
                'datasetSubmission' => $datasetSubmission,
                'researchGroupList' => $researchGroupList,
                'datasetSubmissionLockStatus' => $datasetSubmissionLockStatus
            )
        );
    }

    /**
     * Load XML into Dataset from file.
     *
     * @param UploadedFile|string    $xmlURI            The file containing the XML.
     * @param DatasetSubmission      $datasetSubmission The dataset submission that will be populated with XML data.
     * @param EntityManagerInterface $entityManager     A Pelagos EntityManager.
     *
     * @throws InvalidMetadataException When the file is not Simple XML.
     *
     * @return void
     */
    private function loadFromXml($xmlURI, DatasetSubmission $datasetSubmission, EntityManagerInterface $entityManager)
    {
        $xml = simplexml_load_file($xmlURI, 'SimpleXMLElement', (LIBXML_NOERROR | LIBXML_NOWARNING));

        if ($xml instanceof \SimpleXMLElement and 'MI_Metadata' == $xml->getName()) {
            $this->clearDatasetSubmission($datasetSubmission);

            ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                $xml,
                $datasetSubmission,
                $entityManager
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
        $datasetSubmission->getMetadataContacts()->clear();
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

    /**
     * Determines whether the dataset submission is locked or not.
     *
     * @param Dataset $dataset An instance of the object Dataset.
     *
     * @return boolean
     */
    private function isSubmissionLocked(Dataset $dataset)
    {
        if (
            in_array($dataset->getDatasetStatus(), [Dataset::DATASET_STATUS_BACK_TO_SUBMITTER, Dataset::DATASET_STATUS_NONE])
            and !$dataset->getResearchGroup()->isLocked()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Get the correct dataset submission depending on the state.
     *
     * @param Dataset $dataset The dataset for which submission is retrieved.
     *
     * @return mixed|null|DatasetSubmission
     */
    private function getDatasetSubmission(Dataset $dataset)
    {
        $datasetSubmission = (($dataset->getDatasetSubmissionHistory()->first()) ? $dataset->getDatasetSubmissionHistory()->first() : null);

        if ($datasetSubmission and $datasetSubmission->getStatus() !== DatasetSubmission::STATUS_INCOMPLETE) {
            // Added so that it doesn't conflict with dataset review record.
            $datasetSubmission = $dataset->getDatasetSubmission();
        }
        return $datasetSubmission;
    }

    /**
     * Add/Update distribution Point with default distribution values linked to this datasetSubmission.
     *
     * @param DatasetSubmission $datasetSubmission A dataset submission instance.
     * @param string            $udi               The UDI entered by the user to generate distributionUrl.
     *
     * @throws \Exception When there is none or more than one defaultDistribution organization with given name.
     *
     * @return DatasetSubmission A datasetsubmission.
     */
    private function defaultDistributionPoint(DatasetSubmission $datasetSubmission, string $udi)
    {
        $defaultDistributionContacts = $this->entityHandler->getBy(
            DataCenter::class,
            array('emailAddress' => self::DEFAULT_DISTRIBUTION_POINT_CONTACT_EMAIL)
        );

        if (count($defaultDistributionContacts) === 1) {
            $distributionPoints = $datasetSubmission->getDistributionPoints();
            if ($distributionPoints->isEmpty()) {
                $distributionPoint = new DistributionPoint();
                $datasetSubmission->addDistributionPoint($distributionPoint);
            } else {
                $distributionPoint = $datasetSubmission->getDistributionPoints()->first();
            }

            //update to default values only when the Distribution Point is new
            if (null === $distributionPoint->getDataCenter()) {
                $distributionPoint->setRoleCode(self::DEFAULT_DISTRIBUTION_POINT_ROLECODE);
                $distributionPoint->setDataCenter($defaultDistributionContacts[0]);
                $distributionPoint->setDistributionUrl(
                    $this->generateUrl(
                        'pelagos_app_ui_dataland_default',
                        ['udi' => $udi],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                );
            }
        } else {
            throw new \Exception('There is none or more than one default distribution contact(s)');
        }
        return $datasetSubmission;
    }

    /**
     * Info page for Dataset Submission.
     *
     * @Route("/dataset-submission/how-to-submit-data", name="pelagos_app_ui_datasetsubmission_info", methods={"GET"})
     *
     * @return Response A Response instance.
     */
    public function submissionInfoPage(): Response
    {
        return $this->render("DatasetSubmission/how-to-submit.html.twig");
    }
}
