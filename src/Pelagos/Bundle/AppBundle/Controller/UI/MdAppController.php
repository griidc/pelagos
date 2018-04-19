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
     * Download original user-submitted raw metadata file.
     *
     * @param integer $id The Pelagos ID of the metadata's associated dataset.
     *
     * @Route("/download-orig-raw-xml/{id}")
     *
     * @return XML|string
     */
    public function downloadMetadataFromOriginalFile($id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        $dataStoreUtil = $this->get('pelagos.util.data_store');

        $dataset = $entityHandler->get(Dataset::class, $id);
        if (null === $dataset) {
            $response = new Response('This dataset could not be found.');
            return $response;
        }

        $metadataFile = $dataStoreUtil->getDownloadFileInfo($dataset->getUdi(), 'metadata');

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (null === $datasetSubmission) {
            $response = new Response('Could not find dataset submission for dataset.');
            return $response;
        }

        $originalMetadataFilename = $datasetSubmission->getMetadataFileName();
        if (null === $originalMetadataFilename) {
            $response = new Response('No metadata filename found in dataset submission.');
            return $response;
        }

        $metadataFilePathName = $metadataFile->getRealPath();
        if (false === $metadataFilePathName) {
            $response = new Response("File $metadataFilePathName not available.");
            return $response;
        } else {
            $response = new BinaryFileResponse($metadataFilePathName);
            $response->headers->set('Content-Disposition', 'attachment; filename=' . $originalMetadataFilename . ';');
            return $response;
        }
    }

    /**
     * Download metadata from persistance.
     *
     * @param string $id The ID of Dataset associated with desired Metadata.
     *
     * @Route("/download-db-xml/{id}")
     *
     * @throws \Exception If SimpleXml not extractable from Metadata.
     * @throws \Exception If conversion to string from SimpleXml fails.
     * @return XML|string
     */
    public function downloadMetadataFromDB($id)
    {
        $entityHandler = $this->get('pelagos.entity.handler');

        // Since ID is passed via URL, this could happen via end user action.
        $dataset = $entityHandler->get(Dataset::class, $id);
        if (null === $dataset) {
            $response = new Response('This dataset could not be found.');
            return $response;
        }

        $metadataXml = $this->get('pelagos.util.metadata')->getXmlRepresentation($dataset);

        $windowsFilenameSafeUdi = str_replace(':', '-', $dataset->getUdi());
        $response = new Response($metadataXml);
        $response->headers->set('Content-Disposition', 'attachment; filename='
            . $windowsFilenameSafeUdi . '-metadata.xml;');
        return $response;
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
        $mdappLogger = $this->get('pelagos.util.mdapplogger');
        $dataset = $entityHandler->get(Dataset::class, $id);
        $from = $dataset->getMetadataStatus();
        $udi = $dataset->getUdi();
        $to = $request->request->get('to');

        $message = null;
        if (null !== $to) {
            if ('Accepted' == $to) {
                $datasetSubmission = $dataset->getDatasetSubmission();
                $datasetSubmission->setMetadataStatus($to);
                $entityHandler->update($datasetSubmission);
                $entityHandler->update($dataset);
                $mdappLogger->writeLog($this->getUser()->getUsername() .
                    'has changed status for ' . $udi . '(' . $this->getFlashBagStatus($from) . '->'
                    . $this->getFlashBagStatus($to) . '(mdapp msg))');
                $message = 'Status for ' . $udi . 'has been changed from ' . $this->getFlashBagStatus($from) . ' to '
                    . $this->getFlashBagStatus($to);
                $this->container->get('pelagos.event.entity_event_dispatcher')->dispatch(
                    $datasetSubmission,
                    'approved'
                );
            } else {
                $datasetSubmission = $dataset->getDatasetSubmission();
                $datasetSubmission->setMetadataStatus($to);
                $entityHandler->update($datasetSubmission);
                $entityHandler->update($dataset);
                $mdappLogger->writeLog($this->getUser()->getUsername() .
                    'has changed status for ' . $udi . '(' . $this->getFlashBagStatus($from) . '->'
                    . $this->getFlashBagStatus($to) . '(mdapp msg))');
                $message = 'Status for ' . $udi . ' has been changed from ' . $this->getFlashBagStatus($from) . ' to '
                    . $this->getFlashBagStatus($to);
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
