<?php

namespace App\Controller\UI;

use App\Repository\DatasetRepository;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * A controller for producing dataset monitoring reports.
 */
class ReportDatasetMonitoring extends ReportController
{
    #[Route('/dataset-monitoring-report', name: 'pelagos_app_ui_report_dataset_monitoring_csv')]
    public function researchGroupReport(Request $request, DatasetRepository $datasetRepository, SerializerInterface $serializer): Response
    {
        $researchGroupId = $request->query->get('researchGroup');
        $fundingCycleId = $request->query->get('fundingCycle');
        $fundingOrganizationId = $request->query->get('fundingOrganization');

        $datasets = $datasetRepository->getDatasetsBy(
            researchGroup: $researchGroupId,
            fundingCycle: $fundingCycleId,
            fundingOrganization: $fundingOrganizationId
        );

        $csvFilename = 'DatasetMonitoringReport' . '-' .
            (new \DateTime('now'))->format('Y-m-d') .
            '.csv';

        $response = new Response($serializer->serialize($datasets, 'csv', ["output_utf8_bom" => true]));

        $response->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $csvFilename)
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Encoding', 'UTF-8');

        return $response;
    }
}