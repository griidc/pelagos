<?php

namespace App\Controller\UI;

use App\Dto\DatasetMonitoringCsvRow;
use App\Repository\DatasetRepository;
use App\Repository\FundingCycleRepository;
use App\Repository\FundingOrganizationRepository;
use App\Repository\ResearchGroupRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf as KnpSnappyPdf;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * A controller for producing dataset monitoring reports.
 */
class ReportDatasetMonitoring extends ReportController
{
    /**
     * Returns a Dataset Monitoring Report in CSV.
     */
    #[Route('/dataset-monitoring-report-old-csv', name: 'pelagos_app_ui_report_dataset_monitoring_old_csv')]
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

        $csvFilename = 'DatasetMonitoringReport-' .
            (new \DateTime('now'))->format('Ymd\THis') .
            '.csv';

        $response = new Response($serializer->serialize($datasets, 'csv', ['output_utf8_bom' => true]));

        $response->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $csvFilename)
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Encoding', 'UTF-8');

        return $response;
    }

    /**
     * Returns PDF results of datasets for requested FO/FC/RG.
     */
    #[Route('/dataset-monitoring-report-pdf', name: 'pelagos_app_ui_report_dataset_monitoring_pdf')]
    public function getDatasetsAsPdf(Request $request, KnpSnappyPdf $knpSnappyPdf): Response
    {
        $request->query->add(['makePdf' => true]);

        $pageUrl = $this->generateUrl('app_api_dataset_monitoring_datasets', $request->query->all(), UrlGeneratorInterface::ABSOLUTE_URL);

        $pdfFilename = 'DatasetMonitoringReport-' .
            (new \DateTime('now'))->format('Ymd\THis') .
            '.pdf';

        return new PdfResponse(
            $knpSnappyPdf->getOutput($pageUrl),
            $pdfFilename
        );
    }

    /**
     * Returns a Dataset Monitoring Report in CSV.
     */
    #[Route('/dataset-monitoring-report-csv', name: 'pelagos_app_ui_report_dataset_monitoring_csv')]
    public function getDatasetsAsCSV(
        Request $request,
        DatasetRepository $datasetRepository, 
        ResearchGroupRepository $researchGroupRepository,
        FundingCycleRepository $fundingCycleRepository,
        FundingOrganizationRepository $fundingOrganizationRepository,
        SerializerInterface $serializer
    ): Response
    {

        $researchGroupId = $request->query->get('researchGroup');
        $fundingCycleId = $request->query->get('fundingCycle');
        $fundingOrganizationId = $request->query->get('fundingOrganization');

        /** @var DatasetMonitoringCsvRow[] $reportRows */
        $reportRows = [];

        // Base retrieval logic based the most specific granularity of the request parameters provided.
        if ($researchGroupId !== null && $researchGroupId !== '') {
            $reportRows = array_merge(
                $reportRows,
                $this->getDatasetsByResearchGroup((int) $researchGroupId, $researchGroupRepository)
            );
        } elseif ($fundingCycleId !== null && $fundingCycleId !== '') {
            $fundingCycle = $fundingCycleRepository->find((int) $fundingCycleId);
            if ($fundingCycle) {
                $researchGroups = $fundingCycle->getResearchGroups();
                foreach ($researchGroups as $researchGroup) {
                    $reportRows = array_merge(
                        $reportRows,
                        $this->getDatasetsByResearchGroup($researchGroup->getId(), $researchGroupRepository)
                    );
                }
            } 
        } elseif ($fundingOrganizationId !== null && $fundingOrganizationId !== '') {
            $fundingOrganization = $fundingOrganizationRepository->find((int) $fundingOrganizationId);
            if ($fundingOrganization) {
                $fundingCycles = $fundingOrganization->getFundingCycles();
                foreach ($fundingCycles as $fundingCycle) {
                    $researchGroups = $fundingCycle->getResearchGroups();
                    foreach ($researchGroups as $researchGroup) {
                        $reportRows = array_merge(
                            $reportRows,
                            $this->getDatasetsByResearchGroup($researchGroup->getId(), $researchGroupRepository)
                        );
                    }
                }
            }
        } else {
            // If no specific filter is provided, retrieve all datasets
            $datasets = $datasetRepository->findAll();
            foreach ($datasets as $dataset) {
                array_push($reportRows, DatasetMonitoringCsvRow::fromDataset($dataset));
            }
        }

        $csvFilename = 'DatasetMonitoringReport-' .
            (new \DateTime('now'))->format('Ymd\THis') .
            '.csv';
        
        $headerRow = implode(',', [
            'UDI',
            'DOI',
            'Title',
            'Status',
            'Research Group',
            'Funding Cycle',
            'Funding Organization',
        ])."\r\n";

        $context = [
            'no_headers' => true, // allows override of Entity/DTO headers with our own header row
            'csv_end_of_line' => "\r\n",
        ];

        $csvBody = $serializer->serialize($reportRows, 'csv', $context);
        // Prepend UTF-8 BOM to ensure proper encoding in Excel 
        // This is kind of ugly, but works, unlike the Symfony 
        // serializer's 'output_utf8_bom' option, which doesn't 
        // work in this case because I'm manually overriding the
        // headers since I really don't like the Entity/DTO headers
        // that the serializer generates by default.
        $response = new Response("\xEF\xBB\xBF".$headerRow.$csvBody);

        $response->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $csvFilename)
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Encoding', 'UTF-8');

        return $response;
    }

    /**
     * Returns CSV data datasets belonging to specified Research Group. If none,
     * returns a row DTO with just the Research Group, Funding Cycle, and Funding Organization.
     *
     * @param integer $researchGroupId
     * @param ResearchGroupRepository $researchGroupRepository
     * @return DatasetMonitoringCsvRow[]
     */
    private function getDatasetsByResearchGroup(
        int $researchGroupId, 
        ResearchGroupRepository $researchGroupRepository,
    ): array
    {
        $researchGroup = $researchGroupRepository->find($researchGroupId);

        /* @var DatasetMonitoringCsvRow[] $datasetRows */
        $datasetRows = [];

        if ($researchGroup) {
            $datasets = $researchGroup->getDatasets();
            if ($datasets->count() > 0) {
                foreach ($datasets as $dataset) {
                    $datasetRows[] = DatasetMonitoringCsvRow::fromDataset($dataset);
                }
            } else {
                    $datasetRows[] = DatasetMonitoringCsvRow::emptyDatasetRow(
                        researchGroup: $researchGroup->getName(),
                        fundingCycle: $researchGroup->getFundingCycle()->getName(),
                        fundingOrganization: $researchGroup->getFundingCycle()->getFundingOrganization()->getName(),
                    ); 
            }
        }
        return $datasetRows;
    }   
}
