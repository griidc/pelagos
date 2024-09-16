<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Entity\FundingCycle;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use App\Handler\EntityHandler;
use App\Repository\DatasetRepository;
use App\Repository\FundingCycleRepository;
use App\Repository\FundingOrganizationRepository;
use App\Repository\ResearchGroupRepository;
use App\Util\FundingOrgFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @param EntityHandler $entityHandler the entity handler
     */
    public function __construct(EntityHandler $entityHandler)
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * The default action.
     */
    #[Route('/dataset-monitoring', name: 'pelagos_app_ui_datasetmonitoring_default')]
    public function index(): Response
    {
        return $this->render('DatasetMonitoring/index.html.twig');
    }

    /**
     * This will return a plain item list with FOs, FCs, RGs as JSON.
     */
    #[Route('/api/groups', name: 'app_api_dataset_monitoring_groups')]
    public function getGroups(FundingOrganizationRepository $fundingOrganizationRepository, FundingOrgFilter $fundingOrgFilter): Response
    {
        $filter = array();
        if ($fundingOrgFilter->isActive()) {
            $filter = array('id' => $fundingOrgFilter->getFilterIdArray());
        }

        $fundingOrganizations = $fundingOrganizationRepository->findBy($filter, array('name' => 'ASC'));

        $list = [];

        foreach ($fundingOrganizations as $fundingOrganization) {
            $fundingOrganizationName = $fundingOrganization->getName();
            $fundingOrganizationId = 'fundingOrganization' . $fundingOrganization->getId();
            $list[] =
                [
                    'id' => $fundingOrganizationId,
                    'name' => $fundingOrganizationName,
                    'fundingOrganization' => $fundingOrganization->getId(),
                    'datasets' => $fundingOrganization->getDatasets()->count(),
                    'expanded' => count($fundingOrganizations) == 1,
                ];
            $fundingCycles = $fundingOrganization->getFundingCycles();
            foreach ($fundingCycles as $fundingCycle) {
                $fundingCycleName = $fundingCycle->getName();
                $fundingCycleId = 'fundingCycle' . $fundingCycle->getId();

                $list[] = [
                    'id' => $fundingCycleId,
                    'name' => $fundingCycleName,
                    'parent' => $fundingOrganizationId,
                    'fundingCycle' => $fundingCycle->getId(),
                    'datasets' => $fundingCycle->getDatasets()->count(),
                    'expanded' => count($fundingCycles) == 1 and count($fundingOrganizations) == 1,
                ];
                foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                    $researchGroupId = 'researchGroup' . $researchGroup->getId();
                    $researchGroupName = $researchGroup->getName();
                    $list[] = [
                        'id' => $researchGroupId,
                        'name' => $researchGroupName,
                        'parent' => $fundingCycleId,
                        'researchGroup' => $researchGroup->getId(),
                        'datasets' => $researchGroup->getDatasets()->count(),
                    ];
                }
            }
        }

        return new JsonResponse($list);
    }

    /**
     * Returns HTML results of datasets for requested FO/FC/RG
     */
    #[Route('/dataset-monitoring/datasets', name: 'app_api_dataset_monitoring_datasets')]
    public function getDatasets(
        Request $request,
        FundingOrganizationRepository $fundingOrganizationRepository,
        FundingCycleRepository $fundingCycleRepository,
        ResearchGroupRepository $researchGroupRepository
    ): Response {
        $fundingOrganizationId = $request->query->get('fundingOrganization');
        $fundingCycleId = $request->query->get('fundingCycle');
        $researchGroupId = $request->query->get('researchGroup');
        $datasetFilter = $request->query->get('datasetFilter');
        $makePdf = (bool) $request->query->get('makePdf');
        $fundingOrganization = (!empty($fundingOrganizationId)) ? $fundingOrganizationRepository->find($fundingOrganizationId) : null;
        $fundingCycle = (!empty($fundingCycleId)) ? $fundingCycleRepository->find($fundingCycleId) : null;
        $researchGroup = (!empty($researchGroupId)) ? $researchGroupRepository->find($researchGroupId) : null;

        if ($makePdf) {
            return $this->render(
                'DatasetMonitoring/v2/pdf.html.twig',
                [
                    'fundingOrganization' => $fundingOrganization,
                    'fundingCycle' => $fundingCycle,
                    'researchGroup' => $researchGroup,
                    'datasetFilter' => $datasetFilter,
                    'makePdf' => $makePdf,
                ]
            );
        }

        return $this->render(
            'DatasetMonitoring/v2/datasets.html.twig',
            [
                'fundingOrganization' => $fundingOrganization,
                'fundingCycle' => $fundingCycle,
                'researchGroup' => $researchGroup,
                'datasetFilter' => $datasetFilter,
                'makePdf' => $makePdf,
            ]
        );
    }

    /**
     * Returns HTML results of datasets for requested FO/FC/RG
     */
    #[Route('/dataset-monitoring/datasets/json', name: 'app_api_dataset_monitoring_datasets_json')]
    public function getDatasetsAsJson(Request $request, DatasetRepository $datasetRepository): Response
    {
        $researchGroupId = $request->query->get('researchGroup');
        $fundingCycleId = $request->query->get('fundingCycle');
        $fundingOrganizationId = $request->query->get('fundingOrganization');

        $datasets = $datasetRepository->getDatasetsBy(
            researchGroup: $researchGroupId,
            fundingCycle: $fundingCycleId,
            fundingOrganization: $fundingOrganizationId
        );

        return new JsonResponse($datasets);
    }

    /**
     * The Dataset Monitoring display all research groups of a Funding Cycle.
     *
     * @param int    $id       a Pelagos Funding Cycle entity id
     * @param string $renderer either 'browser' or 'html2pdf'
     *
     * @Route("/dataset-monitoring/funding-cycle/{id}/{renderer}", name="pelagos_app_ui_datasetmonitoring_allresearchgroup", defaults={"renderer" = "browser"})
     *
     * @return Response a Response instance
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
                [
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                ]
            );
        } else {
            return $this->render(
                'DatasetMonitoring/projects.html.twig',
                [
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                    'id' => $id,
                ]
            );
        }
    }

    /**
     * The Dataset Monitoring display by research group.
     *
     * @param int    $id       a Pelagos Research Group entity id
     * @param string $renderer either 'browser' or 'html2pdf'
     *
     * @Route("/dataset-monitoring/research-group/{id}/{renderer}", name="pelagos_app_ui_datasetmonitoring_researchgroup")
     *
     * @return Response a Response instance
     */
    public function researchGroupAction(int $id, string $renderer = 'browser')
    {
        $researchGroup = $this->entityHandler->get(ResearchGroup::class, $id);
        $title = $researchGroup->getName();
        $pdfFilename = 'Dataset Monitoring-' . date('Y-m-d');
        if ('html2pdf' == $renderer) {
            return $this->render(
                'DatasetMonitoring/pdf.html.twig',
                [
                    'researchGroups' => [$researchGroup],
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                ]
            );
        } else {
            return $this->render(
                'DatasetMonitoring/projects.html.twig',
                [
                    'researchGroups' => [$researchGroup],
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                    'id' => $id,
                ]
            );
        }
    }

    /**
     * The Dataset Monitoring display by a researcher.
     *
     * @param int    $id       a Pelagos Person entity id of a researcher
     * @param string $renderer either 'browser' or 'html2pdf'
     *
     * @Route("/dataset-monitoring/researcher/{id}/{renderer}", name="pelagos_app_ui_datasetmonitoring_researcher")
     *
     * @return Response a Response instance
     */
    public function researcherAction(int $id, string $renderer = 'browser')
    {
        $researcher = $this->entityHandler->get(Person::class, $id);
        $title = $researcher->getLastName() . ', ' . $researcher->getFirstName();
        $researchGroups = $researcher->getResearchGroups();
        if ('html2pdf' == $renderer) {
            return $this->render(
                'DatasetMonitoring/pdf.html.twig',
                [
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' .
                        $researcher->getLastName() .
                        ' ' .
                        $researcher->getFirstName(),
                ]
            );
        } else {
            return $this->render(
                'DatasetMonitoring/projects.html.twig',
                [
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' .
                        $researcher->getLastName() .
                        ' ' .
                        $researcher->getFirstName(),
                    'id' => $id,
                ]
            );
        }
    }

    /**
     * The Dataset Monitoring details per UDI.
     *
     * @param string $udi a UDI
     *
     * @Route("/dataset-monitoring/dataset_details/{udi}", name="pelagos_app_ui_datasetmonitoring_datasetdetails")
     *
     * @return Response a Response instance
     */
    public function datasetDetailsAction(string $udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, ['udi' => $udi]);

        return $this->render(
            'DatasetMonitoring/dataset_details.html.twig',
            [
                'datasets' => $datasets,
                ]
        );
    }
}
