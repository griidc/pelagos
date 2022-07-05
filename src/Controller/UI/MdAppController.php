<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\MdappType;
use Doctrine\ORM\Query;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Handler\EntityHandler;
use App\Util\MdappLogger;

/**
 * The MDApp controller.
 */
class MdAppController extends AbstractController
{
    /**
     * A Doctine ORM Entity Handler.
     *
     * @var EntityHandler entityHandler
     */
    protected $entityHandler;

    /**
     * JIRA issue tracking base url.
     *
     * @var string
     */
    protected $issueTrackingBaseUrl;

    /**
     * Class constructor.
     *
     * @param EntityHandler $entityHandler        Pelagos EntityHandler instance.
     * @param string        $issueTrackingBaseUrl JIRA issue tracking base url.
     */
    public function __construct(EntityHandler $entityHandler, string $issueTrackingBaseUrl)
    {
        $this->entityHandler = $entityHandler;
        $this->issueTrackingBaseUrl = $issueTrackingBaseUrl;
    }

    /**
     * MDApp UI.
     *
     * @Route(
     *      "/mdapp",
     *      name="pelagos_app_ui_mdapp_default",
     *      methods={"GET"}
     * )
     *
     * @return Response
     */
    public function defaultAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        return $this->renderUi();
    }

    /**
     * Change the dataset status.
     *
     * This function called when Post occurs upon submitt of the MdApp form.
     *
     * @param Request     $request     The Symfony request object.
     * @param MdappLogger $mdappLogger The Pelagos Mdapp logger.
     * @param integer     $id          The id of the Dataset to change the dataset status for.
     *
     * @Route(
     *      "/mdapp/change-dataset-status/{id}",
     *      name="pelagos_app_ui_mdapp_changedatasetstatus",
     *      methods={"POST"}
     * )
     *
     * @return Response
     */
    public function changeDatasetStatusAction(Request $request, MdappLogger $mdappLogger, int $id)
    {
        $dataset = $this->entityHandler->get(Dataset::class, $id);
        $message = null;
        $from = $dataset->getDatasetStatus();
        $to = $request->request->get('to');
        $udi = $dataset->getUdi();
        if ($dataset instanceof Dataset) {
            $datasetSubmission = (($dataset->getDatasetSubmissionHistory()->first()) ? $dataset->getDatasetSubmissionHistory()->first() : null);

            if ($datasetSubmission instanceof DatasetSubmission and $datasetSubmission->getStatus() !== DatasetSubmission::STATUS_INCOMPLETE) {
                $datasetSubmission = $dataset->getDatasetSubmission();
                if (null !== $to and 'InReview' == $to) {
                    $datasetSubmission->setDatasetStatus($to);
                    $this->entityHandler->update($datasetSubmission);
                    $this->entityHandler->update($dataset);
                    $mdappLogger->writeLog($this->getUser()->getUsername() . ' changed status for ' .
                        $udi . '(' . $this->getFlashBagStatus($from) . ' >>> ' . $this->getFlashBagStatus($to) . ')');
                    $message = 'Status for ' . $udi . ' has been changed from ' . $this->getFlashBagStatus($from) .
                        ' to ' . $this->getFlashBagStatus($to);
                    $this->get('session')->getFlashBag()->add('success', $message);
                }
            } else {
                $message = 'Unable to move the dataset ' . $udi . ' from status Request Revisions to status InReview as it has a unsubmitted draft dataset-submission';
                $this->get('session')->getFlashBag()->add('error', $message);
            }
        }
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
                'MdApp/access-denied.html.twig'
            );
        }

        $objNeeded = array(
            'udi',
            'issueTrackingTicket',
            'datasetSubmission.submissionTimeStamp');

        return $this->render(
            'MdApp/main.html.twig',
            array(
                'issueTrackingBaseUrl' => $this->issueTrackingBaseUrl,
                'm_dataset' => array(
                    'submitted' => $this->entityHandler->getBy(
                        Dataset::class,
                        array('datasetStatus' => Dataset::DATASET_STATUS_SUBMITTED),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'inreview' => $this->entityHandler->getBy(
                        Dataset::class,
                        array('datasetStatus' => Dataset::DATASET_STATUS_IN_REVIEW),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'accepted' => $this->entityHandler->getBy(
                        Dataset::class,
                        array('datasetStatus' => Dataset::DATASET_STATUS_ACCEPTED),
                        array(),
                        $objNeeded,
                        Query::HYDRATE_ARRAY
                    ),
                    'backtosubmitter' => $this->entityHandler->getBy(
                        Dataset::class,
                        array('datasetStatus' => Dataset::DATASET_STATUS_BACK_TO_SUBMITTER),
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
     * @param string      $udi         The dataset UDI identifier.
     * @param MdappLogger $mdappLogger The Logger utility.
     *
     * @Route("/mdapp/getlog/{udi}",
     *      name="pelagos_app_ui_mdapp_getlog"
     * )
     *
     * @return response
     */
    public function getlog(string $udi, MdappLogger $mdappLogger)
    {
        $data = null;
        $entries = $mdappLogger->getLogEntriesByUdi($udi);

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
    private function getFlashBagStatus(string $status)
    {
        if (array_key_exists($status, Dataset::DATASET_STATUSES)) {
            $status = Dataset::DATASET_STATUSES[$status];
        }

        return $status;
    }
}
