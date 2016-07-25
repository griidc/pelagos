<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
class MdAppController extends UIController
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
        return $this->renderUi();
    }

    /**
     * Change the metadata status.
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
        $metadataUtil = $this->get('pelagos.util.metadata');
        $dataset = $entityHandler->get(Dataset::class, $id);
        $from = $dataset->getMetadataStatus();
        $udi = $dataset->getUdi();
        $to = $request->request->get('to');
        if (null !== $to) {
            $dataset->getDatasetSubmission()->setMetadataStatus($to);
            $entityHandler->update($dataset);
            $metadataUtil->writeLog($this->getUser()->getUsername() . " has changed metadata status for $udi ($from -> $to)");
        }
        return $this->renderUi();
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
            'datasetSubmission.creationTimeStamp',
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
                    'secondcheck' => $entityHandler->getBy(
                        Dataset::class,
                        array('metadataStatus' => DatasetSubmission::METADATA_STATUS_SECOND_CHECK),
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
}
