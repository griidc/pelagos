<?php

namespace App\Controller\UI;

use App\Entity\File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\DatasetSubmissionType;
use App\Handler\EntityHandler;
use App\Event\EntityEventDispatcher;
use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\DatasetLink;
use App\Entity\DatasetSubmission;
use App\Entity\DatasetSubmissionReview;
use App\Entity\Entity;
use App\Entity\Fileset;
use App\Entity\Funder;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use App\Entity\PersonDatasetSubmissionMetadataContact;
use App\Message\DatasetSubmissionFiler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 */
class DatasetReviewController extends AbstractController
{
    /**
     * A queue of messages to publish to RabbitMQ.
     *
     * @var array
     */
    protected $messages = array();

    /**
     * The mode in which the dataset-review is opened.
     *
     * @var string
     */
    private $mode;

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
     * The default action for Dataset Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/dataset-review", name="pelagos_app_ui_datasetreview_default", methods={"GET"})
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect(
                $this->generateUrl('security_login') . '?destination='
                . $this->generateUrl('pelagos_app_ui_datasetreview_default')
            );
        }

        $dataset = null;
        $datasetSubmission = null;
        $reviewModes = array('view', 'review');

        $udi = $request->query->get('udiReview');
        $mode = $request->query->get('mode');


        if (null !== $udi) {
            if (!empty($mode) and in_array($mode, $reviewModes)) {
                $this->mode = $mode;
            } else {
                $this->mode = 'view';
            }
            $userAuthCheck = $this->authForReview();

            if (!$userAuthCheck) {
                return $this->render('template/AdminOnly.html.twig');
            }

            return $this->eligibiltyForReview($udi, $request);
        }

        return $this->render(
            'DatasetReview/index.html.twig',
            array(
                'udi' => $udi,
                'dataset' => $dataset,
                'datasetSubmission' => $datasetSubmission,
            )
        );
    }

    /**
     * Checks authorization for the user roles to view/review.
     *
     * @return boolean
     */
    private function authForReview()
    {
        if ('review' === $this->mode) {
            if ($this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
                return true;
            }
        } else {
            if ($this->isGranted('ROLE_DATA_REPOSITORY_MANAGER') or $this->isGranted('ROLE_SUBJECT_MATTER_EXPERT')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks dataset-submissions whether they are eligible for review.
     *
     * @param string  $udi     The UDI entered by the user.
     * @param Request $request The Symfony request object.
     *
     * @return Response A Response instance.
     */
    protected function eligibiltyForReview(string $udi, Request $request)
    {
        $dataset = null;
        $datasetSubmission = null;
        $datasets = $this->entityHandler
            ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));


        if (!empty($datasets)) {
            $dataset = $datasets[0];

            $datasetSubmission = $this->latestDatasetSubmissionForReview($request, $dataset);
        } else {
            $this->addToFlashDisplayQueue($request, $udi, 'notFound');
        }
        return $this->makeSubmissionForm($udi, $dataset, $datasetSubmission);
    }

    /**
     * Gets Latest dataset submission and checks for errors to add in the flash bag.
     *
     * @param Request $request The Symfony request object.
     * @param Dataset $dataset A dataset instance..
     *
     * @return DatasetSubmission  A dataset submission instance.
     */
    private function latestDatasetSubmissionForReview(Request $request, Dataset $dataset)
    {
        $udi = $request->query->get('udiReview');
        $datasetStatus = $dataset->getDatasetStatus();
        $datasetSubmission = $dataset->getLatestDatasetReview();

        if ($datasetStatus === Dataset::DATASET_STATUS_BACK_TO_SUBMITTER) {
            if ('view' === $this->mode) {
                $datasetSubmission = $dataset->getDatasetSubmission();
                $this->addToFlashDisplayQueue($request, $udi, 'backToSub');
            } else {
                $this->addToFlashDisplayQueue($request, $udi, 'requestRevision');
            }
        } elseif ($datasetStatus === Dataset::DATASET_STATUS_NONE) {
            $this->addToFlashDisplayQueue($request, $udi, 'notSubmitted');
        } else {
            if ($datasetSubmission instanceof DatasetSubmission) {
                if ($this->isDatasetBeingProcessed($datasetSubmission)) {
                    $this->addToFlashDisplayQueue($request, $udi, 'processing');
                } else {
                    $datasetSubmissionReview = $datasetSubmission->getDatasetSubmissionReview();
                    if ('review' === $this->mode) {
                        switch (!in_array($datasetStatus, [Dataset::DATASET_STATUS_BACK_TO_SUBMITTER, Dataset::DATASET_STATUS_NONE])) {
                            case (empty($datasetSubmissionReview) || $datasetSubmissionReview->getReviewEndDateTime()):
                                $datasetSubmission = $this->createNewDatasetSubmission($datasetSubmission);
                                break;
                            case (empty($datasetSubmissionReview->getReviewEndDateTime())
                                and $datasetSubmissionReview->getReviewedBy() !== $this->getUser()->getPerson()):
                                $reviewerUserName = $this->entityHandler->get(Account::class, $datasetSubmissionReview->getReviewedBy()->getId())->getUserId();
                                $this->addToFlashDisplayQueue($request, $udi, 'locked', $reviewerUserName);
                                break;
                        }
                    }
                }
            } else {
                $this->addToFlashDisplayQueue($request, $udi, 'notSubmitted');
            }
        }

        return $datasetSubmission;
    }

    /**
     * Add warning messages to flash bag to show it to the user.
     *
     * @param Request $request          The Symfony request object.
     * @param string  $udi              The UDI entered by the user.
     * @param string  $noticeCode       The type of Notice/Error generated, non-numeric.
     * @param string  $reviewerUserName Reviewer Username for the Dataset submission review.
     *
     * @return void
     */
    private function addToFlashDisplayQueue(Request $request, string $udi, string $noticeCode, string $reviewerUserName = null)
    {
        $flashBag = $request->getSession()->getFlashBag();

        $listOfErrors = [
            'notFound' => 'Sorry, the dataset with Unique Dataset Identifier (UDI) ' .
                $udi . ' could not be found. Please email
                        <a href="mailto:griidc@gomri.org?subject=REG Form">griidc@gomri.org</a>
                        if you have any questions.',
            'notSubmitted' => 'The dataset ' . $udi . ' cannot be loaded in review mode at this time because it has not been submitted.',
            'processing' => "The dataset $udi cannot be loaded in review mode at this time because it is still being processed.",
            'hasDraft' => 'The dataset ' . $udi . ' currently has a draft submission and cannot be loaded in review mode.',
            'requestRevision' => 'The status of dataset ' . $udi . ' is Request Revisions and cannot be loaded in review mode.',
            'locked' => 'The dataset ' . $udi . ' is in review mode. Username: ' . $reviewerUserName,
        ];

        $listOfNotices = [
            'backToSub' => "Because this dataset $udi is currently in Request Revisions, you are viewing user's latest data submission.",
        ];

        if (array_key_exists($noticeCode, $listOfErrors)) {
            $flashBag->add('warning', $listOfErrors[$noticeCode]);
        } elseif (array_key_exists($noticeCode, $listOfNotices)) {
            $flashBag->add('notice', $listOfNotices[$noticeCode]);
        }
    }

    /**
     * Make the submission form and return it.
     *
     * @param string            $udi               The UDI entered by the user.
     * @param Dataset           $dataset           The Dataset.
     * @param DatasetSubmission $datasetSubmission The Dataset Submission.
     *
     * @return Response A Response instance.
     */
    protected function makeSubmissionForm(string $udi, Dataset $dataset = null, DatasetSubmission $datasetSubmission = null)
    {
        $datasetSubmissionId = null;
        $researchGroupId = null;
        $datasetSubmissionStatus = null;
        if ($datasetSubmission instanceof DatasetSubmission) {
            if ($datasetSubmission->getDatasetContacts()->isEmpty()) {
                $datasetSubmission->addDatasetContact(new PersonDatasetSubmissionDatasetContact());
            }

            if ($datasetSubmission->getMetadataContacts()->isEmpty()) {
                $datasetSubmission->addMetadataContact(new PersonDatasetSubmissionMetadataContact());
            }

            $datasetSubmissionId = $datasetSubmission->getId();
            $researchGroupId = $dataset->getResearchGroup()->getId();
            $datasetSubmissionStatus = $datasetSubmission->getStatus();

            //Tidy GML.
            $gml = tidy_parse_string(
                $datasetSubmission->getSpatialExtent(),
                array(
                    'input-xml' => true,
                    'output-xml' => true,
                    'indent' => true,
                    'indent-spaces' => 4,
                    'wrap' => 0,
                ),
                'utf8'
            );

            $datasetSubmission->setSpatialExtent($gml);
        }

        $form = $this->formFactory->createNamed(
            '',
            DatasetSubmissionType::class,
            $datasetSubmission,
            array(
                'action' => $this->generateUrl('pelagos_app_ui_datasetreview_post', array('id' => $datasetSubmissionId)),
                'method' => 'POST',
                'attr' => array(
                    'udi' => $udi,
                    'datasetSubmission' => $datasetSubmissionId,
                    'researchGroup' => $researchGroupId,
                    'datasetSubmissionStatus' => $datasetSubmissionStatus,
                    'mode' => $this->mode,
                ),
            )
        );

        // Overwrite the spatial extent field which is normally a hidden type.
        $form->add('spatialExtent', TextareaType::class, array(
            'label' => 'Spatial Extent GML',
            'required' => false,
            'attr' => array(
                'rows' => '10',
                'readonly' => 'true'
            ),
        ));

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
        if (count($researchGroupList) === 0) {
            $researchGroupList = array('!*');
        }

        return $this->render(
            'DatasetReview/index.html.twig',
            array(
                'form' => $form->createView(),
                'udi' => $udi,
                'dataset' => $dataset,
                'datasetSubmission' => $datasetSubmission,
                'researchGroupList' => $researchGroupList,
                'mode' => $this->mode,
                'linkoptions' => DatasetLink::getLinkNameCodeChoices(),
            )
        );
    }

    /**
     * Create a new dataset submission in review mode.
     *
     * @param DatasetSubmission $datasetSubmission The Dataset Submission.
     *
     * @return DatasetSubmission
     */
    private function createNewDatasetSubmission(DatasetSubmission $datasetSubmission)
    {
        // The latest submission is complete, so create new one based on it.
        $datasetSubmission = new DatasetSubmission($datasetSubmission);
        $reviewedBy = $this->getUser()->getPerson();
        $datasetSubmission->setDatasetSubmissionReviewStatus();
        $datasetSubmission->setDatasetStatus(Dataset::DATASET_STATUS_IN_REVIEW);
        $datasetSubmission->setModifier($reviewedBy);
        $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);

        $eventName = 'start_review';

        // Create Dataset submission entity.

        $this->createEntity($datasetSubmission);
        $reviewStartTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $datasetSubmissionReview = new DatasetSubmissionReview($reviewedBy, $reviewStartTimeStamp);
        // Create Dataset submission Review entity for the datatset submission.
        $this->createEntity($datasetSubmissionReview);

        $datasetSubmission->setDatasetSubmissionReview($datasetSubmissionReview);
        $this->entityHandler->update($datasetSubmission);

        $this->entityEventDispatcher->dispatch(
            $datasetSubmission,
            $eventName
        );

        return $datasetSubmission;
    }

    /**
     * Create an entity for each new review.
     *
     * @param Entity $entity A DatasetSubmission or DatasetSubmissionReview to base this DatasetSubmission on.
     *
     * @return void
     */
    private function createEntity(Entity $entity)
    {
        try {
            $this->entityHandler->create($entity);
        } catch (AccessDeniedException $e) {
            // This is handled in the template.
        }
    }

    /**
     * The post action for Dataset Review.
     *
     * @param Request             $request    The Symfony request object.
     * @param integer|null        $id         The id of the Dataset Submission to load.
     * @param MessageBusInterface $messageBus Message bus interface to dispatch messages.
     *
     * @throws BadRequestHttpException When dataset submission has already been submitted.
     * @throws BadRequestHttpException When DIF has not yet been approved.
     *
     * @Route("/dataset-review/{id}", name="pelagos_app_ui_datasetreview_post", methods={"POST"})
     *
     * @return Response A Response instance.
     */
    public function postAction(Request $request, int $id = null, EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        /** @var DatasetSubmission $datasetSubmission */
        $datasetSubmission = $entityManager->getRepository(DatasetSubmission::class)->find($id);

        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect(
                $this->generateUrl('security_login') . '?destination='
                . $this->generateUrl('pelagos_app_ui_datasetreview_default') . '?udiReview='
                . $datasetSubmission->getDataset()->getUdi()
            );
        }
        // set to default event
        $eventName = 'end_review';
        $form = $this->formFactory->createNamed(
            '',
            DatasetSubmissionType::class,
            $datasetSubmission
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            // Clear existing funders
            $dataset = $datasetSubmission->getDataset();
            foreach ($dataset->getFunders() as $funder) {
                $dataset->removeFunder($funder);
            }
            $funderList = $form->get('funders')->getViewData();
            if (false === empty($funderList)) {
                $funderIds = explode(',', $form->get('funders')->getViewData());
                foreach ($funderIds as $funderId) {
                    $funder = $entityManager->getRepository(Funder::class)->findOneBy(['id' => (int) $funderId]);
                    if (!$funder instanceof Funder) {
                        $funder = new Funder();
                        $funder->setName($funderId);
                        $entityManager->persist($funder);
                    }
                    $dataset->addFunder($funder);
                }
            }

            switch (true) {
                case ($form->get('endReviewBtn')->isClicked()):
                    $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_END_REVIEW);
                    $eventName = 'end_review';
                    break;
                case ($form->get('acceptDatasetBtn')->isClicked()):
                    $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_ACCEPT_REVIEW);
                    $eventName = 'accept_review';
                    break;
                case ($form->get('requestRevisionsBtn')->isClicked()):
                    $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_REQUEST_REVISIONS);
                    $eventName = 'request_revisions';
                    break;
            }

            $fileset = $datasetSubmission->getFileset();
            if ($fileset instanceof Fileset) {
                foreach ($fileset->getNewFiles() as $file) {
                    $file->setStatus(File::FILE_IN_QUEUE);
                }
            }

            $reviewedBy = $datasetSubmission->getDatasetSubmissionReview()->getReviewEndedBy()->getFirstName();

            //when request revisions is clicked, do not display the changes made in review for the dataset-submission
            // and get the dataset-submissions which is submitted by the user.
            if ($eventName === 'request_revisions') {
                $fileset = $datasetSubmission->getFileset();
                if ($fileset instanceof Fileset) {
                    // Copy the fileSet
                    $newFileset = new Fileset();
                    foreach ($fileset->getAllFiles() as $file) {
                        $newFile = new File();
                        $newFile->setFilePathName($file->getFilePathName());
                        $newFile->setFileSize($file->getFileSize());
                        $newFile->setFileSha256Hash($file->getFileSha256Hash());
                        $newFile->setUploadedAt($file->getUploadedAt());
                        $newFile->setUploadedBy($file->getUploadedBy());
                        $newFile->setDescription($file->getDescription());
                        $newFile->setPhysicalFilePath($file->getPhysicalFilePath());
                        $newFile->setStatus($file->getStatus());
                        $newFileset->addFile($newFile);
                    }
                    if ($fileset->doesZipFileExist()) {
                        $newFileset->setZipFilePath($fileset->getZipFilePath());
                        $newFileset->setZipFileSha256Hash($fileset->getZipFileSha256Hash());
                        $newFileset->setZipFileSize($fileset->getZipFileSize());
                    }
                    $datasetSubmission = $datasetSubmission->getDataset()->getDatasetSubmission();
                    $datasetSubmission->setFileset($newFileset);
                    $entityManager->persist($newFileset);
                }
            }

            $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_BEING_PROCESSED);

            $entityManager->flush();

            // update MDAPP logs after action is executed.
            $this->entityEventDispatcher->dispatch(
                $datasetSubmission,
                $eventName
            );

            $datasetSubmissionFilerMessage = new DatasetSubmissionFiler($datasetSubmission->getId());
            $messageBus->dispatch($datasetSubmissionFilerMessage);

            return $this->render(
                'DatasetReview/submit.html.twig',
                array(
                    'DatasetSubmission' => $datasetSubmission,
                    'reviewedBy' => $reviewedBy
                )
            );
        }
        // This should not normally happen.
        return new Response((string) $form->getErrors(true, false));
    }

    /**
     * To check the if files are being processed
     *
     * @param DatasetSubmission $datasetSubmission A dataset submission instance.
     *
     * @return boolean
     */
    private function isDatasetBeingProcessed(DatasetSubmission $datasetSubmission)
    {
        // List of dataset submission statuses to check.
        $statuses = [DatasetSubmission::STATUS_COMPLETE, DatasetSubmission::STATUS_IN_REVIEW];

        if (
            in_array($datasetSubmission->getStatus(), $statuses)
            and $datasetSubmission->getFileset() instanceof Fileset
            and $datasetSubmission->getDatasetFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_BEING_PROCESSED
        ) {
                return true;
        }

        return false;
    }
}
