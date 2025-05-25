<?php

namespace App\Controller\UI;

use App\Repository\DatasetRepository;
use App\Repository\FundingCycleRepository;
use App\Repository\FundingOrganizationRepository;
use App\Repository\ResearchGroupRepository;
use App\Util\FundingOrgFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The Dataset Monitoring controller.
 */
class DatasetMonitoringController extends AbstractController
{
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
        $filter = [];
        if ($fundingOrgFilter->isActive()) {
            $filter = ['id' => $fundingOrgFilter->getFilterIdArray()];
        }

        $fundingOrganizations = $fundingOrganizationRepository->findBy($filter, ['name' => 'ASC']);

        $list = [];

        foreach ($fundingOrganizations as $fundingOrganization) {
            $fundingOrganizationName = $fundingOrganization->getName();
            $fundingOrganizationId = 'fundingOrganization' . $fundingOrganization->getId();
            $researchGroups = [];
            foreach ($fundingOrganization->getResearchGroups() as $researchGroup) {
                $researchGroups[] = $researchGroup->getId();
            }

            $list[] =
                [
                    'id' => $fundingOrganizationId,
                    'name' => $fundingOrganizationName,
                    'fundingOrganization' => $fundingOrganization->getId(),
                    'datasets' => $fundingOrganization->getDatasets()->count(),
                    'expanded' => 1 == count($fundingOrganizations),
                    'researchGroup' => $researchGroups,
                ];
            $fundingCycles = $fundingOrganization->getFundingCycles();
            foreach ($fundingCycles as $fundingCycle) {
                $fundingCycleName = $fundingCycle->getName();
                $fundingCycleId = 'fundingCycle' . $fundingCycle->getId();
                $researchGroups = [];
                foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                    $researchGroups[] = $researchGroup->getId();
                }

                $list[] = [
                    'id' => $fundingCycleId,
                    'name' => $fundingCycleName,
                    'parent' => $fundingOrganizationId,
                    'fundingCycle' => $fundingCycle->getId(),
                    'datasets' => $fundingCycle->getDatasets()->count(),
                    'expanded' => 1 == count($fundingCycles) and 1 == count($fundingOrganizations),
                    'researchGroup' => $researchGroups,
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
     * Returns HTML results of datasets for requested FO/FC/RG.
     */
    #[Route('/dataset-monitoring/datasets', name: 'app_api_dataset_monitoring_datasets')]
    public function getDatasets(
        FundingOrganizationRepository $fundingOrganizationRepository,
        FundingCycleRepository $fundingCycleRepository,
        ResearchGroupRepository $researchGroupRepository,
        #[MapQueryParameter('fundingOrganization')] ?int $fundingOrganizationId,
        #[MapQueryParameter('fundingCycle')] ?int $fundingCycleId,
        #[MapQueryParameter('researchGroup')] ?int $researchGroupId,
        #[MapQueryParameter] ?string $datasetFilter,
        #[MapQueryParameter] ?bool $makePdf = false,
    ): Response {
        $fundingOrganization = (null !== $fundingOrganizationId) ? $fundingOrganizationRepository->find($fundingOrganizationId) : null;
        $fundingCycle = (null !== $fundingCycleId) ? $fundingCycleRepository->find($fundingCycleId) : null;
        $researchGroup = (null !== $researchGroupId) ? $researchGroupRepository->find($researchGroupId) : null;

        return $this->render(
            'DatasetMonitoring/v2/dataset-monitoring.html.twig',
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
     * Returns HTML results of datasets for requested FO/FC/RG.
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
}
