<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\DatasetSubmissionReview;
use Pelagos\Entity\Entity;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-review")
 */
class DatasetReviewController extends UIController implements OptionalReadOnlyInterface
{
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
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
        
        $dataset = null;
        $udi = $request->query->get('udiReview');
        $datasetSubmission = null;

        if ($udi !== null) {
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

            $datasetSubmission = $dataset->getDatasetSubmissionHistory()->first();
            $dif = $dataset->getDif();
            $datasetSubmissionStatus = $datasetSubmission->getStatus();
            $datasetSubmissionMetadataStatus = $dataset->getMetadataStatus();

            if ($datasetSubmission instanceof DatasetSubmission) {

                if ($datasetSubmissionStatus === DatasetSubmission::STATUS_COMPLETE and
                    $datasetSubmissionMetadataStatus !== DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER) {

                    $this->createNewDatasetSubmission($datasetSubmission);

                } elseif ($datasetSubmissionStatus === DatasetSubmission::STATUS_IN_REVIEW and
                $datasetSubmissionMetadataStatus === DatasetSubmission::METADATA_STATUS_IN_REVIEW or
                $datasetSubmissionMetadataStatus === DatasetSubmission::METADATA_STATUS_SUBMITTED) {
                    //TODO: Create new Entity Review and add attributes to check whether it is in review and locked //
                    $datasetSubmissionReview = $datasetSubmission->getDatasetSubmissionReview();

                    $valid = $this->checkLocked($datasetSubmissionReview);

                    if ($valid) {
                        $this->createNewDatasetSubmission($datasetSubmission);
                    } else {
                        $error = 5;
                        $this->addToFlashBag($request, $udi, $error);
                    }

                } else {
                    $this->checkErrors($request, $datasetSubmissionStatus, $datasetSubmissionMetadataStatus, $udi);
                }
            } else {
                $this->checkErrors($request, $datasetSubmissionStatus, $datasetSubmissionMetadataStatus, $udi);
            }
        } else {
            $error = 1;
            $this->addToFlashBag($request, $udi, $error);
        }

        return $this->makeSubmissionForm($udi, $dataset, $datasetSubmission);
    }

    /**
     * Check for errors to add in the flash bag.
     *
     * @param Request $request                         The Symfony request object.
     * @param string  $datasetSubmissionStatus         The status of the dataset submission.
     * @param string  $datasetSubmissionMetadataStatus The metadata status of the dataset.
     * @param string  $udi                             The UDI entered by the user.
     *
     * @return void
     */
    private function checkErrors(Request $request, $datasetSubmissionStatus, $datasetSubmissionMetadataStatus, $udi)
    {
        if ($datasetSubmissionStatus === DatasetSubmission::STATUS_INCOMPLETE) {
            $error = 3;
            $this->addToFlashBag($request, $udi, $error);
        } elseif ($datasetSubmissionMetadataStatus === DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER) {
            $error = 4;
            $this->addToFlashBag($request, $udi, $error);
        } else {
            $error = 2;
            $this->addToFlashBag($request, $udi, $error);
        }
    }

    /**
     * Add error messages to flash bag to show it to the user.
     *
     * @param Request $request The Symfony request object.
     * @param string  $udi     The UDI entered by the user.
     * @param integer $error   The Error code generated.
     *
     * @return void
     */
    private function addToFlashBag(Request $request, $udi, $error)
    {
        $flashBag = $request->getSession()->getFlashBag();

        $listOfErrors = [
            1 => 'Sorry, the dataset with Unique Dataset Identifier (UDI) ' .
                $udi . ' could not be found. Please email 
                        <a href="mailto:griidc@gomri.org?subject=REG Form">griidc@gomri.org</a> 
                        if you have any questions.',
            2 => 'The dataset ' . $udi . ' has not been submitted and cannot be loaded in review mode.',
            3 => 'The dataset ' . $udi . ' currently has a draft submission and cannot be loaded in review mode.',
            4 => 'The status of dataset ' . $udi . ' is Back To Submitter and cannot be loaded in review mode.',
            5 => 'The dataset ' . $udi . ' is locked and under review. Please wait for the user to End the Review.'
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
    protected function makeSubmissionForm($udi, Dataset $dataset = null, DatasetSubmission $datasetSubmission = null)
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
                'action' => $this->generateUrl('pelagos_app_ui_datasetreview_post', array('id' => $datasetSubmissionId)),
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
            )
        );

    }

    /**
     * Create a new dataset submission in review mode.
     *
     * @param DatasetSubmission $datasetSubmission The Dataset Submission.
     *
     * @return void
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

            $datasetSubmission->submit($this->getUser()->getPerson());

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
            'body' => $datasetSubmission->getDataset()->getId(),
            'routing_key' => 'dataset.' . $datasetSubmission->getDatasetFileTransferType()
        );
    }

    /**
     * Check whether the dataset-review is locked.
     *
     * @param DatasetSubmissionReview $datasetSubmissionReview A datasetsubmission-review instance for a dataset submission.
     *
     * @return boolean
     */
    private function checkLocked(DatasetSubmissionReview $datasetSubmissionReview)
    {
        if (empty($datasetSubmissionReview)) {
            return true;
        } else {
            if ($datasetSubmissionReview->getReviewEndDateTime() !== null) {
                return true;
            } elseif ($datasetSubmissionReview->getReviewEndDateTime() === null and
                $datasetSubmissionReview->getReviewedBy() === (string) ($this->getUser()->getPerson()->getId())) {
                return true;
            }
        }
        return false;
    }
}
