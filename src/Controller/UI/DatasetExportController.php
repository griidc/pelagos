<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Entity\DOI;
use App\Enum\DatasetLifecycleStatus;
use App\Repository\DatasetRepository;
use App\Util\FundingOrgFilter;
use App\Util\Geometry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Dataset export report generator.
 */
class DatasetExportController extends ReportController
{
    //DateTime format used for date range
    const INREPORT_DATETIMEFORMAT = 'm-d-Y';

    /**
     * This is a parameterless report, so all is in the default action.
     */
    #[Route('/dataset/export')]
    public function generateCsv(DatasetRepository $datasetRepository, FundingOrgFilter $fundingOrgFilter, EntityManagerInterface $entityManager): StreamedResponse
    {
        $data = $this->getData($datasetRepository, $fundingOrgFilter, $entityManager);

        return $this->writeCsvResponse(
            $data,
            'DatasetExport-' .
            (new \DateTime('now'))->format(parent::FILENAME_DATETIMEFORMAT) .
            '.csv'
        );
    }

    /**
     * This method gets data for the report.
     */
    protected function getData(DatasetRepository $datasetRepository, FundingOrgFilter $fundingOrgFilter, EntityManagerInterface $entityManager): array
    {
        $researchGroupIDs = [];
        if ($fundingOrgFilter->isActive()) {
            $researchGroupIDs = $fundingOrgFilter->getResearchGroupsIdArray();
        }

        //Query for datasets, filtered as appropriate.
        $qb = $entityManager->createQueryBuilder();
        $qb
            ->select('d.udi')
            ->from('\App\Entity\Dataset', 'd')
            ->join('\App\Entity\ResearchGroup', 'rg', Join::WITH, 'rg.id = d.researchGroup')
            ->join('\App\Entity\FundingCycle', 'fc', Join::WITH, 'fc.id = rg.fundingCycle')
            ->join('\App\Entity\FundingOrganization', 'fo', Join::WITH, 'fo.id = fc.fundingOrganization');

        $researchGroupIDs = [];
        if ($fundingOrgFilter->isActive()) {
            $researchGroupIDs = $fundingOrgFilter->getResearchGroupsIdArray();
            $qb->andWhere('fo.id IN (:fundingOrg)');
            $qb->setParameter('fundingOrg', $fundingOrgFilter->getFilterIdArray());
        }
        $query = $qb->getQuery();
        $results = $query->getResult();

        $dataArray = [];
        //process result query into an array with organized data
        $currentIndex = 0;
        // used to calculate bounding-box envelope from too-complex for CSV GML.
        $geometryUtil = new Geometry($entityManager);

        foreach ($results as $result) {
            $dataset = $datasetRepository->findOneBy(array('udi' => $result['udi']));

            /** @psalm-suppress PossiblyNullReference, guaranteed by DQL query above. */
            $datasetLifeCycleStatus = $dataset->getDatasetLifecycleStatus();
            if ($datasetLifeCycleStatus === DatasetLifecycleStatus::NONE) {
                continue;
            }

            //initialize array with key  = udi
            if (isset($dataArray[$currentIndex]['udi']) && $result['udi'] != $dataArray[$currentIndex]['udi']) {
                $currentIndex++;
            }
            if (!isset($dataArray[$currentIndex])) {
                $dataArray[$currentIndex] = array(
                    'fundingOrg.name' => null,
                    'fundingCycle.name' => null,
                    'researchGroup.name' => null,
                    'udi' => $result['udi'],
                    'doi' => null,
                    'title' => null,
                    'totalFileSizeMB' => null,
                    'parameters.units' => null,
                    'locationDescription' => null,
                    'spatialExtent' => null,
                    'fileFormat' => null,
                    'timePeriodDescription' => null,
                    'temporalExtent.start' => null,
                    'temporalExtent.end' => null,
                    'themeKeywords' => null,
                    'placeKeywords' => null,
                    'topicKeywords' => null,
                    'LifecycleStatus' => $datasetLifeCycleStatus->value,
                );

                $dataArray[$currentIndex]['fundingOrg.name'] = $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getName();
                $dataArray[$currentIndex]['fundingCycle.name'] = $dataset->getResearchGroup()->getFundingCycle()->getName();
                $dataArray[$currentIndex]['researchGroup.name'] = $dataset->getResearchGroup()->getName();
                if ($dataset->getDoi() instanceof DOI) {
                    $dataArray[$currentIndex]['doi'] = $dataset->getDoi()->getDoi();
                }
                $dataArray[$currentIndex]['title'] = $dataset->getTitle();
                $dataArray[$currentIndex]['totalFileSizeMB'] = ($dataset->getTotalFileSize()) / 1000 ** 2;
                $dataArray[$currentIndex]['parameters.units'] = $dataset->getDatasetSubmission()?->getSuppParams();
                $dataArray[$currentIndex]['locationDescription'] = $dataset->getDatasetSubmission()?->getSpatialExtentDescription();
                if ($dataset?->getSpatialExtentGeometry() !== null) {
                    $dataArray[$currentIndex]['spatialExtent'] = $geometryUtil->calculateEnvelopeFromGml($dataset->getSpatialExtentGeometry());
                };
                $dataArray[$currentIndex]['fileFormat'] = $dataset->getDatasetSubmission()?->getDistributionFormatName();
                $dataArray[$currentIndex]['timePeriodDescription'] = $dataset->getDatasetSubmission()?->getTemporalExtentDesc();
                $dataArray[$currentIndex]['temporalExtent.start'] = $dataset->getDatasetSubmission()?->getTemporalExtentBeginPosition()?->format(self::INREPORT_DATETIMEFORMAT);
                $dataArray[$currentIndex]['temporalExtent.end'] = $dataset->getDatasetSubmission()?->getTemporalExtentEndPosition()?->format(self::INREPORT_DATETIMEFORMAT);
                $dataArray[$currentIndex]['themeKeywords'] = $dataset->getDatasetSubmission()?->getThemeKeywordsString();
                $dataArray[$currentIndex]['placeKeywords'] = $dataset->getDatasetSubmission()?->getPlaceKeywordsString();
                $dataArray[$currentIndex]['topicKeywords'] = $dataset->getDatasetSubmission()?->getTopicKeywordsString();
            }
        }
        return array_merge($this->getDefaultHeaders(), $this->getLabels(), $dataArray);
    }

    /**
     * Get labels for the report.
     *
     * @param string $reportName Name of the report.
     *
     * @return array
     */
    private function getLabels(): array
    {
        //prepare labels
        $labels = ['labels' => [
            'fundingOrg.name',
            'fundingCycle.name',
            'researchGroup.name',
            'udi',
            'doi',
            'title',
            'totalFileSizeMB',
            'parameters.units',
            'locationDescription',
            'spatialExtentEnvelope',
            'fileFormat',
            'timePeriodDescription',
            'temporalExtent.start',
            'temporalExtent.end',
            'themeKeywords',
            'placeKeywords',
            'topicKeywords',
        ]];
        return $labels;
    }

    /**
     * This returns the Report name extracted from the controller name and a creation timestamp.
     *
     * @return array Report Name to be displayed and a creation time stamp for the csv
     */
    protected function getDefaultHeaders()
    {
        //generic report name extracted from the controller's name
        $reportNameCamelCase = preg_replace('/Controller$/', '', (new \ReflectionClass($this))->getShortName());
        return array(
            array(trim(strtoupper(preg_replace('/(?<!\ )[A-Z]/', ' $0', $reportNameCamelCase)))),
            array('CREATED AT', (new \DateTime('now'))->format(self::INREPORT_DATETIMEFORMAT)),
            array(self::BLANK_LINE)
        );
    }
}
