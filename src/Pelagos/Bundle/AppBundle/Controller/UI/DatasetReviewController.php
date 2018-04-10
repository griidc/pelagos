<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;
use Pelagos\Entity\DatasetSubmissionReview;
use Pelagos\Entity\Entity;
use Pelagos\Entity\Account;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-review")
 */
class DatasetReviewController extends UIController implements OptionalReadOnlyInterface
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
     * The default action for Dataset Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $dataset = null;
        $datasetSubmission = null;
        $reviewModes = array('view', 'review');

        $udi = $request->query->get('udiReview');
        $mode = $request->query->get('mode');


        if ($udi !== null) {
            if (!empty($mode) and in_array($mode, $reviewModes)) {
               $this->mode = $mode;
            } else {
                $this->mode = 'view';
            }

            $userAuthCheck = $this->authForReview();

            if (!$userAuthCheck) {
                return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
            }

            return $this->eligibiltyForReview($udi, $request);
        }

        return $this->render(
            'PelagosAppBundle:DatasetReview:index.html.twig',
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
     * @return bool
     */
    private function authForReview()
    {
        if ($this->mode === 'review') {
            if ($this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
                return true;
            }
        } else {
            if ($this->isGranted(array('ROLE_DATA_REPOSITORY_MANAGER', 'ROLE_SUBJECT_MATTER_EXPERT'))) {
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
    protected function eligibiltyForReview($udi, Request $request)
    {
        $dataset = null;
        $datasetSubmission = null;
        $datasets = $this->entityHandler
            ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));


        if (!empty($datasets)) {
            $dataset = $datasets[0];

            $datasetSubmission = (($dataset->getDatasetSubmissionHistory()->first()) ? $dataset->getDatasetSubmissionHistory()->first() : null);

            if ($datasetSubmission instanceof DatasetSubmission and $this->filerStatus($datasetSubmission)) {

                $datasetSubmission = $this->latestDatasetSubmissionForReview($request, $datasetSubmission, $dataset, $udi);

            } else {
                $this->addToFlashBag($request, $udi, 'notSubmitted');
            }
        } else {
            $this->addToFlashBag($request, $udi, 'notFound');
        }

        return $this->makeSubmissionForm($udi, $dataset, $datasetSubmission, $request);
    }

    /**
     * Gets Latest dataset submission and checks for errors to add in the flash bag.
     *
     * @param Request           $request           The Symfony request object.
     * @param DatasetSubmission $datasetSubmission A dataset submission instance.
     * @param Dataset           $dataset           A dataset instance..
     * @param string            $udi               The UDI entered by the user.
     *
     * @return DatasetSubmission  A dataset submission instance.
     */
    private function latestDatasetSubmissionForReview(Request $request, DatasetSubmission $datasetSubmission, Dataset $dataset, $udi)
    {
        $datasetSubmissionStatus = (($datasetSubmission) ? $datasetSubmission->getStatus() : null);
        $datasetSubmissionMetadataStatus = $dataset->getMetadataStatus();

        switch (true) {

            case ($datasetSubmissionStatus === DatasetSubmission::STATUS_INCOMPLETE):
                $this->addToFlashBag($request, $udi, 'hasDraft');
                break;

            case ($datasetSubmissionMetadataStatus === DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER):
                $this->addToFlashBag($request, $udi, 'backToSub');
                break;

            default:
                $datasetSubmission = $this->reviewMode($request, $datasetSubmission, $dataset, $udi);
                break;
        }

        return $datasetSubmission;
    }

    /**
     * Add error messages to flash bag to show it to the user.
     *
     * @param Request $request          The Symfony request object.
     * @param string  $udi              The UDI entered by the user.
     * @param integer $error            The Error code generated.
     * @param string  $reviewerUserName Reviewer Username for the Dataset submission review.
     *
     * @return void
     */
    private function addToFlashBag(Request $request, $udi, $error, $reviewerUserName = null)
    {
        $flashBag = $request->getSession()->getFlashBag();

        $listOfErrors = [
            'notFound' => 'Sorry, the dataset with Unique Dataset Identifier (UDI) ' .
                $udi . ' could not be found. Please email
                        <a href="mailto:griidc@gomri.org?subject=REG Form">griidc@gomri.org</a>
                        if you have any questions.',
            'notSubmitted' => 'The dataset ' . $udi . ' cannot be loaded in review mode at this time because it has not been submitted or it is still being processed.',
            'hasDraft' => 'The dataset ' . $udi . ' currently has a draft submission and cannot be loaded in review mode.',
            'backToSub' => 'The status of dataset ' . $udi . ' is Back To Submitter and cannot be loaded in review mode.',
            'locked' => 'The dataset ' . $udi . ' is in review mode. Username: ' . $reviewerUserName,
        ];

        if (array_key_exists($error, $listOfErrors)) {
            $flashBag->add('warning', $listOfErrors[$error]);
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
    protected function makeSubmissionForm($udi, Dataset $dataset = null, DatasetSubmission $datasetSubmission = null, Request $request)
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
        }

        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $datasetSubmission,
            array(
                'action' => $this->generateUrl('pelagos_app_ui_datasetreview_post', array('id' => $datasetSubmissionId)),
                'method' => 'POST',
                'attr' => array(
                    'datasetSubmission' => $datasetSubmissionId,
                    'researchGroup' => $researchGroupId,
                    'datasetSubmissionStatus' => $datasetSubmissionStatus,
                    'mode' => $this->mode,
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
                    if ($dataset->getDatasetSubmission() instanceof DatasetSubmission and
                        $datasetSubmission->getDatasetFileUri() === $dataset->getDatasetSubmission()->getDatasetFileUri()) {
                        $showForceImport = true;
                    }
                    break;
                case DatasetSubmission::TRANSFER_TYPE_HTTP:
                    $form->get('datasetFileUrl')->setData($datasetSubmission->getDatasetFileUri());
                    if ($dataset->getDatasetSubmission() instanceof DatasetSubmission and
                        $datasetSubmission->getDatasetFileUri() === $dataset->getDatasetSubmission()->getDatasetFileUri()) {
                        $showForceDownload = true;
                    }
                    break;
            }
        }

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
            'PelagosAppBundle:DatasetReview:index.html.twig',
            array(
                'form' => $form->createView(),
                'udi' => $udi,
                'dataset' => $dataset,
                'datasetSubmission' => $datasetSubmission,
                'showForceImport' => $showForceImport,
                'showForceDownload' => $showForceDownload,
                'researchGroupList' => $researchGroupList,
                'mode' => $this->mode,
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
        $reviewStartTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $datasetSubmissionReview = new DatasetSubmissionReview($datasetSubmission, $reviewedBy, $reviewStartTimeStamp);
        $datasetSubmission->setDatasetSubmissionReviewStatus();
        $datasetSubmission->setMetadataStatus(DatasetSubmission::METADATA_STATUS_IN_REVIEW);
        $datasetSubmission->setModifier($reviewedBy);
        $eventName = 'in_review';

        $this->container->get('pelagos.event.entity_event_dispatcher')->dispatch(
            $datasetSubmission,
            $eventName
        );

        // Create Dataset submission entity.

        $this->createEntity($datasetSubmission);

        // Create Dataset submission Review entity for the datatset submission.
        $this->createEntity($datasetSubmissionReview);

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
        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $datasetSubmission
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {

            $this->processDatasetFileTransferDetails($form, $datasetSubmission);

            if ($this->getUser()->isPosix()) {
                $incomingDirectory = $this->getUser()->getHomeDirectory() . '/incoming';
            } else {
                $incomingDirectory = $this->getParameter('homedir_prefix') . '/upload/'
                    . $this->getUser()->getUserName() . '/incoming';
                if (!file_exists($incomingDirectory)) {
                    mkdir($incomingDirectory, 0755, true);
                }
            }

            switch (true) {
                case ($form->get('endReviewBtn')->isClicked()):
                    $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_END_REVIEW);
                    break;
                case ($form->get('acceptDatasetBtn')->isClicked()):
                    $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_ACCEPT_REVIEW);
                    break;
                case ($form->get('requestRevisionsBtn')->isClicked()):
                    $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_REQUEST_REVISIONS);
                    break;
            }

            $this->entityHandler->update($datasetSubmission->getDatasetSubmissionReview());

            $this->entityHandler->update($datasetSubmission);

            foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
                $this->entityHandler->update($datasetContact);
            }

            foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
                $this->entityHandler->update($metadataContact);
            }

            //use rabbitmq to process dataset file and persist the file details.
            foreach ($this->messages as $message) {
                $this->get('old_sound_rabbit_mq.dataset_submission_producer')->publish(
                    $message['body'],
                    $message['routing_key']
                );
            }

            return $this->render(
                'PelagosAppBundle:DatasetReview:submit.html.twig',
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
            'body' => $datasetSubmission->getId(),
            'routing_key' => 'dataset.' . $datasetSubmission->getDatasetFileTransferType()
        );
    }

    /**
     * To check the filer status of a previous datasetsubmission/review.
     *
     * @param DatasetSubmission $datasetSubmission A dataset submission instance.
     *
     * @return boolean
     */
    private function filerStatus(DatasetSubmission $datasetSubmission)
    {
        // List of dataset submission statuses to check.
        $statuses = [DatasetSubmission::STATUS_COMPLETE, DatasetSubmission::STATUS_IN_REVIEW];

        if (in_array($datasetSubmission->getStatus(), $statuses)) {
            switch (true) {
                case ($datasetSubmission->getDatasetFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_NONE):
                    return false;
                    break;
                case ($datasetSubmission->getDatasetFileTransferStatus() === DatasetSubmission::TRANSFER_STATUS_COMPLETED and empty($datasetSubmission->getDatasetFileSha256Hash())):
                    return false;
                    break;
            }
        }
        return true;
    }

    /**
     * Loads the appropriate dataset submission based on which mode is selected.
     *
     * @param Request           $request           The Symfony request object.
     * @param DatasetSubmission $datasetSubmission A dataset submission instance.
     * @param Dataset           $dataset           A dataset instance..
     * @param string            $udi               The UDI entered by the user.
     *
     * @return DatasetSubmission  A dataset submission instance.
     */
    private function reviewMode(Request $request, DatasetSubmission $datasetSubmission, Dataset $dataset, $udi)
    {
        $datasetSubmissionStatus = (($datasetSubmission) ? $datasetSubmission->getStatus() : null);
        $datasetSubmissionMetadataStatus = $dataset->getMetadataStatus();
        if ($this->mode === 'view') {
            return $datasetSubmission;
        } elseif ($this->mode === 'review') {
            switch (true) {
                case ($datasetSubmissionStatus === DatasetSubmission::STATUS_COMPLETE and $datasetSubmissionMetadataStatus !== DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER):
                    $datasetSubmission = $this->createNewDatasetSubmission($datasetSubmission);
                    break;

                case ($datasetSubmissionStatus === DatasetSubmission::STATUS_IN_REVIEW and ($datasetSubmissionMetadataStatus === DatasetSubmission::METADATA_STATUS_IN_REVIEW or $datasetSubmissionMetadataStatus === DatasetSubmission::METADATA_STATUS_SUBMITTED)):
                    $datasetSubmissionReview = $datasetSubmission->getDatasetSubmissionReview();
                    switch (true) {
                        case (empty($datasetSubmissionReview) || $datasetSubmissionReview->getReviewEndDateTime()):
                            $datasetSubmission = $this->createNewDatasetSubmission($datasetSubmission);
                            break;
                        case (empty($datasetSubmissionReview->getReviewEndDateTime()) and $datasetSubmissionReview->getReviewedBy() !== $this->getUser()->getPerson()):
                            $reviewerUserName  = $this->entityHandler->get(Account::class, $datasetSubmissionReview->getReviewedBy())->getUserId();
                            $this->addToFlashBag($request, $udi, 'locked', $reviewerUserName);
                            break;
                    }
                    break;
            }
        }

        return $datasetSubmission;
    }
}
