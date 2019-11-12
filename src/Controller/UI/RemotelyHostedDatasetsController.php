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
     * @Route("/remotelyhosted-datasets", name="pelagos_app_ui_remotelyhosteddatasets_default", methods={"GET"})
     *
     * @return Response A response instance.
     */
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
     * @Route("/remotelyhosted-datasets/{udi}", name="pelagos_app_ui_remotelyhosteddatasets_post", methods={"POST"})
     *
     * @return Response
     */
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
            $datasetSubmission = $dataset->getDatasetSubmission();
            $datasetStatus = $dataset->getDatasetStatus();

            if ($datasetStatus === Dataset::DATASET_STATUS_ACCEPTED) {
                if (DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED !== $datasetSubmission->getDatasetFileTransferStatus()) {
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);

                    $this->dispatchLogEvent($dataset, $this->getUser()->getUserId());
                    return new Response('Dataset UDI ' . $udi . ' has been successfully set to remotely hosted.', Response::HTTP_OK);
                } else {
                    $message = 'Dataset UDI ' . $udi . ' is already set to remotely hosted.';
                }
            } else {
                $message = 'Unable to set dataset UDI ' . $udi . ' to remotely hosted. Dataset status must be ACCEPTED.';
            }
        } else {
            $message = 'Invalid UDI!';
        }
        //return 202 Accepted for accepted but not processed request
        return new Response($message, Response::HTTP_ACCEPTED);
    }

    /**
     * Get the Dataset Url from a given udi.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/remotelyhosted-datasets/{udi}", name="pelagos_app_ui_remotelyhosteddatasets_geturl", methods={"GET"})
     *
     * @return Response
     */
    public function getUrlAction(Request $request)
    {
        $udi = $request->attributes->get('udi');
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        $responseMsg = '';
        if (!empty($datasets)) {
            $dataset = $datasets[0];
            $datasetSubmission = $dataset->getDatasetSubmission();
            if (null !== $datasetSubmission) {
                $responseMsg = $datasetSubmission->getDatasetFileUri();
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
}
