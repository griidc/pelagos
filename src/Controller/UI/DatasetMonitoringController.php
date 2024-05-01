<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Entity\DIF;
use App\Entity\FundingCycle;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use App\Handler\EntityHandler;
use App\Repository\DatasetRepository;
use App\Repository\FundingOrganizationRepository;
use JsonException;
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
     * Dataset Life Cycle Statuses
     */
    public const DATASET_LIFECYCLE_STATUS_NONE = 'none';
    public const DATASET_LIFECYCLE_STATUS_IDENTIFIED = 'identified';
    public const DATASET_LIFECYCLE_STATUS_SUBMITTED = 'submitted';
    public const DATASET_LIFECYCLE_STATUS_ACCEPTED = 'accepted';
    public const DATASET_LIFECYCLE_STATUS_RESTRICTED = 'restricted';

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
    #[Route('//dataset-monitoring', name: 'pelagos_app_ui_datasetmonitoring_default')]
    public function index(): Response
    {
        return $this->render('DatasetMonitoring/index.html.twig');
    }

    /**
     * This will return a plain item list with FOs, FCs, RGs as JSON.
     */
    #[Route('/api/groups', name: 'app_api_dataset_monitoring_groups')]
    public function getGroups(FundingOrganizationRepository $fundingOrganizationRepository): Response
    {
        $fundingOrganizations = $fundingOrganizationRepository->findAll();

        $list = [];

        foreach ($fundingOrganizations as $fundingOrganization) {
            $fundingOrganizationName = $fundingOrganization->getName();
            $fundingOrganizationId = 'fundingOrganization' . $fundingOrganization->getId();
            $list[] =
                [
                    'id' => $fundingOrganizationId,
                    'name' => $fundingOrganizationName,
                    'fundingOrganization' => $fundingOrganization->getId(),
                ];
            foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
                $fundingCycleName = $fundingCycle->getName();
                $fundingCycleId = 'fundingCycle' . $fundingCycle->getId();
                $list[] = [
                    'id' => $fundingCycleId,
                    'name' => $fundingCycleName,
                    'parent' => $fundingOrganizationId,
                    'fundingCycle' => $fundingCycle->getId(),
                ];
                foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                    $researchGroupId = 'researchGroup' . $researchGroup->getId();
                    $researchGroupName = $researchGroup->getName();
                    $list[] = [
                        'id' => $researchGroupId,
                        'name' => $researchGroupName,
                        'parent' => $fundingCycleId,
                        'researchGroup' => $researchGroup->getId(),
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
    public function getDatasets(Request $request, DatasetRepository $datasetRepository): Response
    {
        $fundingOrganizationId = $request->query->get('fundingOrganization');
        $fundingCycleId = $request->query->get('fundingCycle');
        $researchGroupId = $request->query->get('researchGroup');

        $datasets = $datasetRepository->getDatasetsBy(
            $fundingOrganizationId,
            $fundingCycleId,
            $researchGroupId
        );

        // dd($datasets);

        return $this->render(
            'DatasetMonitoring/v2/datasets.html.twig',
            [
                'datasets' => $datasets,
            ]
        );
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

    /**
     * Get the Dataset's Lifecycle Status
     */
    #[Route('/api/dataset/{udi}/lifecyclestatus', name: 'app_api_dataset_lifecycle_status')]
    public function getDatasetLifecycleStatus(string $udi): JsonResponse
    {
        # Lifecycle Status Definitions:
        # None - Dataset has no approved DIF, although UDI has been internally established.
        # Identified - Dataset has approved DIF, but no submission
        # Submitted - Dataset has a submission, not a draft. Does not have to be approved, nor in-review, etc.
        # Accepted - Dataset has a submission, and submission is excepted, and acceptance has not been revoked back to "in review"
        # Restricted (subset of Accepted) - Criterial for accepted, but also has the restricted flag set.

        $dataset = $this->container->get('doctrine')->getRepository(Dataset::class)->findOneBy(array('udi' => $udi));

        if ($dataset instanceof Dataset) {
            if (($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED) and ($dataset->isRestricted() === false)) {
                $datasetLifeCycleStatus = self::DATASET_LIFECYCLE_STATUS_ACCEPTED;
            } elseif ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED) {
                $datasetLifeCycleStatus = self::DATASET_LIFECYCLE_STATUS_RESTRICTED;
            } elseif ($dataset->hasDatasetSubmission()) {
                $datasetLifeCycleStatus = self::DATASET_LIFECYCLE_STATUS_SUBMITTED;
            } elseif ($dataset->hasDif() and $dataset->getDif()->getStatus() == DIF::STATUS_APPROVED) {
                $datasetLifeCycleStatus = self::DATASET_LIFECYCLE_STATUS_IDENTIFIED;
            } else {
                $datasetLifeCycleStatus = self::DATASET_LIFECYCLE_STATUS_NONE;
            }
            return new JsonResponse($datasetLifeCycleStatus);
        } else {
            return new JsonResponse("Dataset not found", 404);
        }
    }
}
