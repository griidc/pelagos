<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\EndReviewType;

use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DatasetSubmissionReview;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The end review tool helps to end the review of a dataset submission review.
 *
 * @Route("/end-review")
 */
class EndReviewController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action for End Review.
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

        $form = $this->get('form.factory')->createNamed(
            'endReview',
            EndReviewType::class
        );

        $form->handleRequest($request);

        $udi = $form->getData()['datasetUdi'];

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateAndEndReview($udi, $request);
        }

        return $this->render('PelagosAppBundle:EndReview:default.html.twig', array('form' => $form->createView()));
    }

    /**
     * To validate and end the review of a dataset submissionr review.
     *
     * @param string  $udi     Dataset UDI identifier.
     * @param Request $request A Symfony request object.
     *
     * @return void
     */
    private function validateAndEndReview($udi, Request $request)
    {
        $datasets = $this->entityHandler
            ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));
        //  are there Datasets in the array
        if (!empty($datasets)) {
            $dataset = $datasets[0];
            //  If the first element in the array is of type Dataset get the DatasetSubmission
            if ($dataset instanceof Dataset) {
                $datasetSubmission = (($dataset->getDatasetSubmissionHistory()->first()) ? $dataset->getDatasetSubmissionHistory()->first() : null);
                //  if it is of type DatasetSubmission object get the DatasetSubmissionReview element
                if ($datasetSubmission instanceof DatasetSubmission) {
                    $datasetSubmissionReview = $datasetSubmission->getDatasetSubmissionReview();
                    //  if it's type  is DatasetSubmissionReview object and
                    //  and the datasetSubmission metadataStatus is "inReview" and
                    //  the the dataset submission review END date-type has not been set
                    //  then call the reviewEvent function of datasetSubmission to change it's state to end the review,
                    //  store / persist the changes
                    //  and send out the messages
                    if ($dataset->getMetadataStatus() === Dataset::DATASET_STATUS_IN_REVIEW and
                        $datasetSubmissionReview instanceof DatasetSubmissionReview and
                        empty($datasetSubmissionReview->getReviewEndDateTime())
                    ) {
                        $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_END_REVIEW);
                        $this->entityHandler->update($datasetSubmissionReview);
                        $this->entityHandler->update($datasetSubmission);
                        $reviewerUserName = $this->entityHandler->get(Account::class, $datasetSubmissionReview->getReviewedBy())->getUserId();
                        $this->addToFlashBag($request, $udi, 'reviewEnded', $reviewerUserName);
                        // update MDAPP logs after action is executed.
                        $this->container->get('pelagos.event.entity_event_dispatcher')->dispatch($datasetSubmission, 'end_review');
                    } else {
                        $this->addToFlashBag($request, $udi, 'notInReview');
                    }
                } else {
                    $this->addToFlashBag($request, $udi, 'notFound');
                }
            } else {
                $this->addToFlashBag($request, $udi, 'notFound');
            }
        } else {
            $this->addToFlashBag($request, $udi, 'notFound');
        }
    }

    /**
     * Add error messages to flash bag to show it to the user.
     *
     * @param Request $request          The Symfony request object.
     * @param string  $udi              The UDI entered by the user.
     * @param string  $flashMessage     The Flashbag message to be showed to the user.
     * @param string  $reviewerUserName Reviewer Username for the Dataset submission review.
     *
     * @return void
     */
    private function addToFlashBag(Request $request, $udi, $flashMessage, $reviewerUserName = null)
    {
        $flashBag = $request->getSession()->getFlashBag();

        $warning = [
            'notFound' => 'Sorry, the dataset with Unique Dataset Identifier (UDI) ' .
                $udi . ' could not be found. Please email
                        <a href="mailto:griidc@gomri.org?subject=REG Form">griidc@gomri.org</a>
                        if you have any questions.',
            'notInReview' => 'The dataset ' . $udi . ' was not in review.',
        ];

        $success = [
            'reviewEnded' => 'The review for dataset ' . $udi . ' that was opened by ' . $reviewerUserName . ' has been terminated.'
        ];

        switch ($flashMessage) {
            case (array_key_exists($flashMessage, $warning)):
                $flashBag->add('warning', $warning[$flashMessage]);
                break;
            case (array_key_exists($flashMessage, $success)):
                $flashBag->add('success', $success[$flashMessage]);
                break;
        }
    }
}
