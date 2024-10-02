<?php

namespace App\Controller\UI;

use App\Event\LogActionItemEventDispatcher;
use App\Handler\EntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DistributionPoint;

/**
 * The Remotely Hosted Datasets list controller.
 */
class RemotelyHostedDatasetsController extends AbstractController
{
    /**
     * Protected entityHandler value instance of entityHandler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * The log action item entity event dispatcher.
     *
     * @var LogActionItemEventDispatcher
     */
    protected $logActionItemEventDispatcher;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityHandler                $entityHandler                The entity handler.
     * @param LogActionItemEventDispatcher $logActionItemEventDispatcher The log action item event dispatcher.
     */
    public function __construct(EntityHandler $entityHandler, LogActionItemEventDispatcher $logActionItemEventDispatcher)
    {
        $this->entityHandler = $entityHandler;
        $this->logActionItemEventDispatcher = $logActionItemEventDispatcher;
    }

    /**
     * Default action of Remotely Hosted Datasets.
     *
     *
     * @return Response A response instance.
     */
    #[Route(path: '/remotelyhosted-datasets', name: 'pelagos_app_ui_remotelyhosteddatasets_default', methods: ['GET'])]
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $GLOBALS['pelagos']['title'] = 'Remotely Hosted Datasets';
        return $this->render('List/RemotelyHostedDatasets.html.twig');
    }

    /**
     * Mark as Remotely Hosted Dataset.
     *
     * @param Request $request The Symfony request object.
     *
     *
     * @return Response
     */
    #[Route(path: '/remotelyhosted-datasets/{udi}', name: 'pelagos_app_ui_remotelyhosteddatasets_post', methods: ['POST'])]
    public function postAction(Request $request)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $udi = $request->attributes->get('udi');
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (!empty($datasets)) {
            $dataset = $datasets[0];
            $datasetStatus = $dataset->getDatasetStatus();
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $preReqs = array();
                // For the following conditions, do not even attempt setting to Remotely Hosted.

                // Dataset must be in Accepted status.
                if (Dataset::DATASET_STATUS_ACCEPTED !== $datasetStatus) {
                    $preReqs[] = 'Unable to set dataset UDI ' . $udi . ' to remotely hosted. Dataset status must be ACCEPTED.';
                }

                // Dataset can't already be set to Remotely Hosted.
                if ($datasetSubmission->isRemotelyHosted()) {
                    $preReqs[] = 'Dataset UDI ' . $udi . ' is already set to remotely hosted.';
                }

                // Additional business rules must be met.
                $preReqs = array_merge($preReqs, $this->remotelyHostedPrerequsiteCheck($datasetSubmission));

                // Only if the previous preconditions are met, mark as Remotely Hosted.
                if (count($preReqs) > 0) {
                    $message = implode(', ', $preReqs);
                    $responseCode = 432;
                } else {
                    $this->dispatchLogEvent($dataset, $this->getUser()->getUserId());
                    $message = 'Dataset UDI ' . $udi . ' has been successfully set to remotely hosted.';
                    $responseCode = Response::HTTP_OK;
                }
            } else {
                $message = 'Dataset is missing submission.';
                $responseCode = 432;
            }
        } else {
            $message = 'Invalid UDI!';
            $responseCode = 432;
        }
        // returning the unassigned 432 code for these error cases.
        return new Response($message, $responseCode);
    }

    /**
     * Get the Dataset Url from a given udi.
     *
     * @param Request $request The Symfony request object.
     *
     *
     * @return Response
     */
    #[Route(path: '/remotelyhosted-datasets/{udi}', name: 'pelagos_app_ui_remotelyhosteddatasets_geturl', methods: ['GET'])]
    public function getUrlAction(Request $request)
    {
        $udi = $request->attributes->get('udi');
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        $responseMsg = '';
        if (!empty($datasets)) {
            $dataset = $datasets[0];
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $responseMsg = $datasetSubmission->getRemotelyHostedUrl();
            }
        }

        return new Response($responseMsg, $responseMsg === '' ? Response::HTTP_NO_CONTENT : Response::HTTP_OK);
    }

    /**
     * Log Mark as Remotely Hosted changes.
     *
     * @param Dataset $dataset The dataset having restrictions modified.
     * @param string  $actor   The username of the person modifying the restriction.
     *
     * @return void
     */
    private function dispatchLogEvent(Dataset $dataset, string $actor)
    {
        $this->logActionItemEventDispatcher->dispatch(
            array(
                'actionName' => 'Mark as Remotely Hosted',
                'subjectEntityName' => 'Pelagos\Entity\Dataset',
                'subjectEntityId' => $dataset->getId(),
                'payLoad' => array(
                    'userId' => $actor,
                    'datasetSubmissionId' => $dataset->getDatasetSubmission()->getId()
                )
            ),
            'remotelyhosted_update_log'
        );
    }

    /**
     * Check to see if the remotely-hosted fields are all populated.
     *
     * @param DatasetSubmission $datasetSubmission A DatasetSubmission.
     *
     * @return array $errors Containing strings of each unpopulated attribute, or empty array for compliant.
     */
    public function remotelyHostedPrerequsiteCheck(DatasetSubmission $datasetSubmission): array
    {
        $errors = array();

        if (empty($datasetSubmission->getRemotelyHostedUrl())) {
            $errors[] = 'Missing Dataset File URL.';
        }
        if (empty($datasetSubmission->getRemotelyHostedName())) {
            $errors[] = 'Missing Remotely-Hosted name.';
        }
        if (empty($datasetSubmission->getRemotelyHostedDescription())) {
            $errors[] = 'Missing Remotly-Hosted Description.';
        }
        if (empty($datasetSubmission->getRemotelyHostedFunction())) {
            $errors[] = 'Missing Remotly-Hosted Function.';
        }

        //(At least one) Distribution URL Matched the Files->Dataset File URL?
        $distributionUrls = array();
        foreach ($datasetSubmission->getDistributionPoints() as $distributionPoint) {
            if ($distributionPoint instanceof DistributionPoint) {
                $distributionUrl = $distributionPoint->getDistributionUrl();
                if (!empty($distributionUrl)) {
                    $distributionUrls[] = $distributionUrl;
                }
            }
        }
        if (!in_array($datasetSubmission->getRemotelyHostedUrl(), $distributionUrls)) {
            $errors[] = 'Dataset File URI does not match distribution URL.';
        }

        return $errors;
    }
}
