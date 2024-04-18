<?php

namespace App\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Handler\EntityHandler;
use App\Entity\Dataset;
use App\Entity\FundingCycle;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use App\Repository\FundingOrganizationRepository;
use App\Util\JsonSerializer;

/**
 * The Dataset Monitoring controller.
 */
class DatasetMonitoringController extends AbstractController
{
    /**
     * Protected entityHandler value instance of entityHandler.
     *
     * @var entityHandler
     */
    protected $entityHandler;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param EntityHandler $entityHandler The entity handler.
     */
    public function __construct(EntityHandler $entityHandler)
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * The default action.
     *
     * @Route("/dataset-monitoring", name="pelagos_app_ui_datasetmonitoring_default", methods={"GET"})
     *
     * @return Response A Symfony Response instance.
     */
    public function defaultAction()
    {
        return $this->render('DatasetMonitoring/index.html.twig');
    }

    #[Route('/api/groups', name: 'app_api_dataset_monitoring_groups')]
    public function index(FundingOrganizationRepository $fundingOrganizationRepository, JsonSerializer $jsonSerializer): Response
    {
        $fundingOrganizations = $fundingOrganizationRepository->findAll();

        return $jsonSerializer->serialize(
            data: $fundingOrganizations,
            groups: ['id', 'monitoring']
        )->createJsonResponse();
    }

    /**
     * The Dataset Monitoring display all research groups of a Funding Cycle.
     *
     * @param integer $id       A Pelagos Funding Cycle entity id.
     * @param string  $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/funding-cycle/{id}/{renderer}", name="pelagos_app_ui_datasetmonitoring_allresearchgroup", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function allResearchGroupAction(int $id, string $renderer = 'browser')
    {
        $fundingCycle = $this->entityHandler->get(FundingCycle::class, $id);
        $title = $fundingCycle->getName();
        $pdfFilename = 'Dataset Monitoring-' . date('Y-m-d');
        $researchGroups = $fundingCycle->getResearchGroups();

        if ('html2pdf' == $renderer) {
            return $this->render(
                'DatasetMonitoring/pdf.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                )
            );
        } else {
            return $this->render(
                'DatasetMonitoring/projects.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                    'id' => $id,
                )
            );
        }
    }

    /**
     * The Dataset Monitoring display by research group.
     *
     * @param integer $id       A Pelagos Research Group entity id.
     * @param string  $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/research-group/{id}/{renderer}", name="pelagos_app_ui_datasetmonitoring_researchgroup")
     *
     * @return Response A Response instance.
     */
    public function researchGroupAction(int $id, string $renderer = 'browser')
    {
        $researchGroup = $this->entityHandler->get(ResearchGroup::class, $id);
        $title = $researchGroup->getName();
        $pdfFilename = 'Dataset Monitoring-' . date('Y-m-d');
        if ('html2pdf' == $renderer) {
            return $this->render(
                'DatasetMonitoring/pdf.html.twig',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                )
            );
        } else {
            return $this->render(
                'DatasetMonitoring/projects.html.twig',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                    'id' => $id,
                )
            );
        }
    }

    /**
     * The Dataset Monitoring display by a researcher.
     *
     * @param integer $id       A Pelagos Person entity id of a researcher.
     * @param string  $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/researcher/{id}/{renderer}", name="pelagos_app_ui_datasetmonitoring_researcher")
     *
     * @return Response A Response instance.
     */
    public function researcherAction(int $id, string $renderer = 'browser')
    {
        $researcher = $this->entityHandler->get(Person::class, $id);
        $title = $researcher->getLastName() . ', ' . $researcher->getFirstName();
        $researchGroups = $researcher->getResearchGroups();
        if ('html2pdf' == $renderer) {
            return $this->render(
                'DatasetMonitoring/pdf.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' .
                        $researcher->getLastName() .
                        ' ' .
                        $researcher->getFirstName()
                )
            );
        } else {
            return $this->render(
                'DatasetMonitoring/projects.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' .
                        $researcher->getLastName() .
                        ' ' .
                        $researcher->getFirstName(),
                    'id' => $id,
                )
            );
        }
    }

    /**
     * The Dataset Monitoring details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @Route("/dataset-monitoring/dataset_details/{udi}", name="pelagos_app_ui_datasetmonitoring_datasetdetails")
     *
     * @return Response A Response instance.
     */
    public function datasetDetailsAction(string $udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        return $this->render(
            'DatasetMonitoring/dataset_details.html.twig',
            array(
                'datasets' => $datasets,
                )
        );
    }
}
