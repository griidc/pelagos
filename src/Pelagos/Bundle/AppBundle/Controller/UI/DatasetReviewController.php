<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;
use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionXmlFileType;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Exception\InvalidMetadataException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pelagos\Entity\Dataset;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @throws BadRequestHttpException When xmlUploadForm is submitted without a file.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $dataset = null;
        $udi = $request->query->get('udiReview');
        $datasetSubmission = null;
        $xmlStatus = array(
            'success' => null,
            'errors' => null,
        );

        if ($udi !== null) {
            $this->checkEligibleForReview($udi, $request);
        }


        return $this->render(
            'PelagosAppBundle:DatasetReview:index.html.twig',
            array(
                'udi' => $udi,
                'xmlStatus' => $xmlStatus,
                'dataset' => $dataset,
                'datasetSubmission' => $datasetSubmission,
            )
        );
    }

    /**
     * @param $udi
     * @param Request $request
     * @return Response
     */
    protected function checkEligibleForReview($udi, Request $request)
    {
        $xmlStatus = null;
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

                if ($datasetSubmissionStatus === DatasetSubmission::STATUS_COMPLETE and
                    $datasetSubmissionMetadataStatus !== DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER) {

                    // The latest submission is complete, so create new one based on it.
                    $datasetSubmission = new DatasetSubmission($datasetSubmission);
                    $datasetSubmission->setDatasetSubmissionReviewStatus();

                    try {
                        $this->entityHandler->create($datasetSubmission);
                    } catch (AccessDeniedException $e) {
                        // This is handled in the template.
                    }

                } elseif ($datasetSubmissionStatus === DatasetSubmission::STATUS_IN_REVIEW) {
                    //TODO: Create new Entity Review and add attributes to check whether datasetSubmission is in review and locked //

                }
            }

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
        } else {
            $error = 1;
            $this->addToFlashBag($request, $udi, $error);
        }

        return $this->makeSubmissionForm($udi, $dataset, $datasetSubmission, $xmlStatus);
    }


    /**
     * @param Request $request
     * @param $udi
     * @param $error
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
            4 => 'The status of dataset ' . $udi . ' is Back To Submitter and cannot be loaded in review mode.'
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
                'PelagosAppBundle:DatasetReview:index.html.twig',
                array(
                    'form' => $form->createView(),
                    'xmlForm' => $xmlFormView,
                    'udi' => $udi,
                    'xmlStatus' => $xmlStatus,
                    'dataset' => $dataset,
                    'datasetSubmission' => $datasetSubmission,
                    'showForceImport' => $showForceImport,
                    'showForceDownload' => $showForceDownload,
                    'researchGroupList' => $researchGroupList,
            )
        );
    }
}
