<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

use Pelagos\Response\TerminateResponse;

/**
 * The Remotely Hosted Datasets list controller.
 *
 * @Route("/remotelyhosted-datasets")
 */
class RemotelyHostedDatasetsController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * Default action of Remotely Hosted Datasets.
     *
     * @Route("")
     *
     * @return Response A response instance.
     */
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $GLOBALS['pelagos']['title'] = 'Remotely Hosted Datasets';
        return $this->render('PelagosAppBundle:List:RemotelyHostedDatasets.html.twig');
    }

    /**
     * Mark as Remotely Hosted Dataset.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/{udi}")
     *
     * @Method("POST")
     *
     * @return TerminateResponse A response.
     */
    public function postAction(Request $request)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
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
                    return new TerminateResponse('Dataset UDI ' . $udi . ' has been successfully set to remotely hosted.', Response::HTTP_OK);
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
        return new TerminateResponse($message, Response::HTTP_ACCEPTED);
    }

    /**
     * Get the Dataset Url from a given udi.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/{udi}")
     *
     * @Method("GET")
     *
     * @return TerminateResponse A response.
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
        return new TerminateResponse($responseMsg, $responseMsg === '' ? 204 : Response::HTTP_OK);
    }

    /**
     * Log Mark as Remotely Hosted changes.
     *
     * @param Dataset $dataset The dataset having restrictions modified.
     * @param string  $actor   The username of the person modifying the restriction.
     *
     * @return void
     */
    private function dispatchLogEvent(Dataset $dataset, $actor)
    {
        $em = $this->container->get('doctrine')->getManager();
        $this->container->get('pelagos.event.log_action_item_event_dispatcher')->dispatch(
            array(
                'actionName' => 'Mark as Remotely Hosted',
                'subjectEntityName' => $em->getClassMetadata(get_class($dataset))->getName(),
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
