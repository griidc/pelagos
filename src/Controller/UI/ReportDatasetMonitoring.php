<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
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
    #[Route('/dataset-monitoring-report-csv', name: 'pelagos_app_ui_report_dataset_monitoring_csv')]
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
     * Test Route.
     */
    #[Route('/dataset-monitoring-test', name: 'pelagos_app_ui_report_dataset_monitoring_test')]
    public function getDatasetsAsCSV(
        Request $request,
        DatasetRepository $datasetRepository, 
        ResearchGroupRepository $researchGroupRepository,
        FundingCycleRepository $fundingCycleRepository,
        FundingOrganizationRepository $fundingOrganizationRepository
    ): Response
    {

        $researchGroupId = $request->query->get('researchGroup');
        $fundingCycleId = $request->query->get('fundingCycle');
        $fundingOrganizationId = $request->query->get('fundingOrganization');
        
        $data = $this->generateCSVHeader() . "\n";

        // Base retrieval logic based the most specific granularity of the request parameters provided.
        if ($researchGroupId) {
            $researchGroup = $researchGroupRepository->find($researchGroupId);
            if ($researchGroup) {
                $datasets = $researchGroup->getDatasets();
                if ($datasets->count() > 0) {
                    foreach ($datasets as $dataset) {
                        $data .= $this->getDatasetCSVData($dataset) . "\n";
                    }
                } else {
                    $researchGroupName = $researchGroup->getName();
                    $data .= ",,,,,$researchGroupName,,";
                }
            }
        } elseif ($fundingCycleId) {
            $fundingCycle = $fundingCycleRepository->find($fundingCycleId);
            if ($fundingCycle) {
                $researchGroups = $fundingCycle->getResearchGroups();
                foreach ($researchGroups as $researchGroup) {
                    $datasets = $researchGroup->getDatasets();
                    foreach ($datasets as $dataset) {
                        $data .= $this->getDatasetCSVData($dataset) . "\n";
                    }
                }
            }
        } else if ($fundingOrganizationId) {
            $fundingOrganization = $fundingOrganizationRepository->find($fundingOrganizationId);
            if ($fundingOrganization) {
                $fundingCycles = $fundingOrganization->getFundingCycles();
                foreach ($fundingCycles as $fundingCycle) {
                    $researchGroups = $fundingCycle->getResearchGroups();
                    foreach ($researchGroups as $researchGroup) {
                        $datasets = $researchGroup->getDatasets();
                        foreach ($datasets as $dataset) {
                            $data .= $this->getDatasetCSVData($dataset) . "\n";
                        }
                    }
                }
            }
        } else {
            // If no specific filter is provided, retrieve all datasets
            $datasets = $datasetRepository->findAll();
            foreach ($datasets as $dataset) {
                $data .= $this->getDatasetCSVData($dataset) . "\n";
            }
        }

        return new Response($data, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dataset.csv"',
        ]);

    }

    private function queryResearchGroups
    private function generateCSVHeader(): string
    {
        $csv = '';
        $csv .= '"UDI",';
        $csv .= '"DOI",';
        $csv .= '"Title",';
        $csv .= '"Status",';
        $csv .= '"Research Group",';
        $csv .= '"Funding Cycle",';
        $csv .= '"Funding Organization"';

        return $csv;
    }

    private function getDatasetCSVData(Dataset $dataset): string
    {
        $researchGroup = $dataset->getResearchGroup();
        $fundingCycle = $researchGroup->getFundingCycle();
        $fundingOrganization = $fundingCycle->getFundingOrganization();

        $csv = '';
        $csv .= '"' . $dataset->getUdi() . '",';
        $csv .= '"' . $dataset->getDoi() . '",';
        $csv .= '"' . $dataset->getTitle() . '",';
        $csv .= '"' . $dataset->getStatus() . '",';
        $csv .= '"' . $researchGroup->getName() . '",';
        $csv .= '"' . $fundingCycle->getName() . '",';
        $csv .= '"' . $fundingOrganization->getName() . '"';

        return $csv;
    }
}
