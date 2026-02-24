<?php

namespace App\Controller\UI;

use App\Event\EntityEventDispatcher;
use App\Form\EndReviewType;
use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DatasetSubmissionReview;
use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The end review tool helps to end the review of a dataset submission review.
 */
class EndReviewController extends AbstractController
{
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
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityHandler         $entityHandler         The entity handler.
     * @param EntityEventDispatcher $entityEventDispatcher The entity event dispatcher.
     */
    public function __construct(EntityHandler $entityHandler, EntityEventDispatcher $entityEventDispatcher)
    {
        $this->entityHandler = $entityHandler;
        $this->entityEventDispatcher = $entityEventDispatcher;
    }

    /**
     * The default action for End Review.
     *
     * @param Request $request The Symfony request object.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/end-review', name: 'pelagos_app_ui_endreview_default')]
    public function defaultAction(Request $request, FormFactoryInterface $formFactory)
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $form = $formFactory->createNamed(
            'endReview',
            EndReviewType::class
        );

        $form->handleRequest($request);

        $udi = $form->getData()['datasetUdi'];

        if ($form->isSubmitted() && $form->isValid()) {
            $this->validateAndEndReview($udi, $request);
        }

        return $this->render('EndReview/default.html.twig', array('form' => $form->createView()));
    }

    /**
     * To validate and end the review of a dataset submissionr review.
     *
     * @param string  $udi     Dataset UDI identifier.
     * @param Request $request A Symfony request object.
     *
     * @return void
     */
    private function validateAndEndReview(string $udi, Request $request)
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
                    //  and the datasetSubmission datasetStatus is "inReview" and
                    //  the the dataset submission review END date-type has not been set
                    //  then call the reviewEvent function of datasetSubmission to change it's state to end the review,
                    //  store / persist the changes
                    //  and send out the messages
                    if (
                        $dataset->getDatasetStatus() === Dataset::DATASET_STATUS_IN_REVIEW and
                        $datasetSubmissionReview instanceof DatasetSubmissionReview and
                        empty($datasetSubmissionReview->getReviewEndDateTime())
                    ) {
                        $datasetSubmission->reviewEvent($this->getUser()->getPerson(), DatasetSubmission::DATASET_END_REVIEW);
                        $this->entityHandler->update($datasetSubmissionReview);
                        $this->entityHandler->update($datasetSubmission);
                        $reviewerUserName = $this->entityHandler->get(Account::class, $datasetSubmissionReview->getReviewedBy()->getId())->getUserId();
                        $this->addToFlashBag($request, $udi, 'reviewEnded', $reviewerUserName);
                        // update MDAPP logs after action is executed.
                        $this->entityEventDispatcher->dispatch($datasetSubmission, 'end_review');
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
    private function addToFlashBag(Request $request, string $udi, string $flashMessage, string $reviewerUserName = null)
    {
        $flashBag = $request->getSession()->getFlashBag();

        $warning = [
            'notFound' => 'Sorry, the dataset with Unique Dataset Identifier (UDI) ' .
                $udi . ' could not be found. Please email
                        <a href="mailto:help@griidc.org?subject=REG Form">help@griidc.org</a>
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
