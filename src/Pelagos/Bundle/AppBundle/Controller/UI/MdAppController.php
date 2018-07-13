<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Pelagos\Bundle\AppBundle\Form\MdappType;

use Doctrine\ORM\Query;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * The MDApp controller.
 *
 * @Route("/mdapp")
 */
class MdAppController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * MDApp UI.
     *
     * @Route("")
     *
     * @return Response
     */
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        return $this->renderUi();
    }

    /**
     * Change the metadata status.
     *
     * This function called when Post occurs upon submitt of the MdApp form.
     *
     * @param Request $request The Symfony request object.
     * @param integer $id      The id of the Dataset to change the metadata status for.
     *
     * @Route("/change-metadata-status/{id}")
     * @Method("POST")
     *
     * @return Response
     */
    public function changeMetadataStatusAction(Request $request, $id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        $mdappLogger = $this->get('pelagos.util.mdapplogger');
        $dataset = $entityHandler->get(Dataset::class, $id);
        $message = null;
        $from = $dataset->getMetadataStatus();
        $to = $request->request->get('to');
        $udi = $dataset->getUdi();
        if ($dataset instanceof Dataset) {
            $datasetSubmission = (($dataset->getDatasetSubmissionHistory()->first()) ? $dataset->getDatasetSubmissionHistory()->first() : null);

            if ($datasetSubmission instanceof DatasetSubmission and $datasetSubmission->getStatus() !== DatasetSubmission::STATUS_INCOMPLETE) {
                $datasetSubmission = $dataset->getDatasetSubmission();
                if (null !== $to and 'InReview' == $to) {
                    $datasetSubmission->setMetadataStatus($to);
                    $entityHandler->update($datasetSubmission);
                    $entityHandler->update($dataset);
                    $mdappLogger->writeLog($this->getUser()->getUsername() . ' changed status for ' .
                        $udi . '(' . $this->getFlashBagStatus($from) . ' >>> ' . $this->getFlashBagStatus($to) . ')');
                    $message = 'Status for ' . $udi . ' has been changed from ' . $this->getFlashBagStatus($from) .
                        ' to ' . $this->getFlashBagStatus($to);
                }

            } else {
                $message = 'Unable to move the dataset ' . $udi . 'from status ' . $from . 'to status ' .$to . 'as it has a unsubmitted draft dataset-submission';
            }
        }
        $this->get('session')->getFlashBag()->add('notice', $message);
        return $this->redirectToRoute('pelagos_app_ui_mdapp_default');
    }
    
    /**
     * Render the UI for MDApp.
     *
     * @return Response
     */
    protected function renderUi()
    {
        // If not DRPM, show Access Denied message.  This is simply for
        // display purposes as the security model is enforced on the
        // object by the handler.
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render(
                'PelagosAppBundle:MdApp:access-denied.html.twig'
            );
        }

        $objNeeded = array(
            'udi',
            'issueTrackingTicket',
            'datasetSubmission.submissionTimeStamp',
            'metadata.id',
            'datasetSubmission.metadataFileName');

        $entityHandler = $this->get('pelagos.entity.handler');
        return $this->render(
            'PelagosAppBundle:MdApp:main.html.twig',
            array(
                'issueTrackingBaseUrl' => $this->getParameter('issue_tracking_base_url'),
                'm_dataset' => array(
                    'submitted' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_SUBMITTED),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'inreview' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_IN_REVIEW),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'accepted' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'backtosubmitter' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                ),
            )
        );
    }

    /**
     * Get logfile entries for particular dataset UDI.
     *
     * @param string $udi The dataset UDI identifier.
     *
     * @Route("/getlog/{udi}")
     *
     * @return response
     */
    public function getlog($udi)
    {

        $rawlog = file($this->getParameter('mdapp_logfile'));
        $entries = array_values(preg_grep("/$udi/i", $rawlog));
        $data = null;
        if (count($entries) > 0) {
            $data .= '<ul>';
            foreach ($entries as $entry) {
                $data .= '<li>' . strip_tags($entry) . "</li>\n";
            }
            $data .= '</ul>';
        }
        $response = new Response();
        $response->setContent($data);
        $response->headers->set('Content-Type', 'text/html');
        return $response;
    }
    
    /**
     * Get the text need to be displayed as DatasetSubmission changed status.
     *
     * @param string $status The datasetSubmission status for the dataset.
     *
     * @return string
     */
    private function getFlashBagStatus($status)
    {
        if (array_key_exists($status, DatasetSubmission::METADATA_STATUSES)) {
            $status = DatasetSubmission::METADATA_STATUSES[$status];
        }

        return $status;
    }
}
