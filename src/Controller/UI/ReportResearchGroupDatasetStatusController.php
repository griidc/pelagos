<?php

namespace App\Controller\UI;

use App\Form\ReportResearchGroupDatasetStatusType;
use App\Entity\ResearchGroup;

use App\Handler\EntityHandler;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A controller for a Report of Research Groups and related Datasets.
 *
 * @return Response A Symfony Response instance.
 */
class ReportResearchGroupDatasetStatusController extends ReportController
{
    // The format used to print the date and time in the report
    const REPORTDATETIMEFORMAT = 'Y-m-d';

    // The format used to put the date and time in the report file name
    const REPORTFILENAMEDATETIMEFORMAT = 'Y-m-d';

    // Limit the research group name to this to keep filename length at 100.
    const MAXRESEARCHGROUPLENGTH = 46;

    /**
     * The default action.
     *
     * @param Request       $request         Message information for this Request.
     * @param EntityHandler $entityHandler   The entity handler.
     * @param integer       $researchGroupId The identifier for the Research Group subject of the report.
     *
     * @Route("/report-researchgroup-dataset-status", name="pelagos_app_ui_reportresearchgroupdatasetstatus_default")
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    public function defaultAction(Request $request, EntityHandler $entityHandler, int $researchGroupId = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        //  fetch all the Research Groups
        $allResearchGroups = $entityHandler->getAll(ResearchGroup::class, array('name' => 'ASC'));
        //  put all the names in an array with the associated doctrine id
        $researchGroupNames = array();
        foreach ($allResearchGroups as $rg) {
            $researchGroupNames[$rg->getName()] = $rg->getId();
        }
        $form = $this->get('form.factory')->createNamed(
            null,
            ReportResearchGroupDatasetStatusType::class,
            $researchGroupNames
        );

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $researchGroupId = $form->getData()['ResearchGroupSelector'];
                $researchGroup = $entityHandler
                   ->getBy(ResearchGroup::class, ['id' => $researchGroupId])[0];

                return $this->writeCsvResponse(
                    $this->getData(['researchGroup' => $researchGroup]),
                    $this->createCsvReportFileName($researchGroup->getName(), $researchGroupId)
                );
            }
        }
        return $this->render(
            'Reports/ReportResearchGroupDatasetStatus.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Research group dataset status report for dataset monitoring.
     *
     * @param Request       $request         Message information for this Request.
     * @param EntityHandler $entityHandler   The entity handler.
     *
     * @Route(
     *     "/report-researchgroup/dataset-monitoring",
     *     name="pelagos_app_ui_reportresearchgroupdatasetstatus_datasetmonitoringreport",
     *     methods={"GET"}
     *     )
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    public function datasetMonitoringReportAction(Request $request, EntityHandler $entityHandler)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        //  fetch all the Research Groups
        $allResearchGroups = $entityHandler->getAll(ResearchGroup::class, array('name' => 'ASC'));
        //  put all the names in an array with the associated doctrine id
        $researchGroupNames = array();
        foreach ($allResearchGroups as $rg) {
            $researchGroupNames[$rg->getName()] = $rg->getId();
        }
        $form = $this->get('form.factory')->createNamed(
            null,
            ReportResearchGroupDatasetStatusType::class,
            $researchGroupNames
        );

        $form->handleRequest($request);

        return $this->render(
            'Reports/ReportResearchGroupDatasetStatus.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * The post action for Dataset Submission.
     *
     * @param Request       $request         The Symfony request object.
     *
     * @Route(
     *     "/report-researchgroup/dataset-monitoring",
     *     name="pelagos_app_ui_datasetsubmission_post",
     *     methods={"POST"}
     *     )
     *
     * @return Response A Response instance.
     */
    public function postAction(Request $request)
    {
        $researchGroupId = $request->get('ResearchGroupSelector');
        $researchGroup = $this->container->get('doctrine')->getRepository(ResearchGroup::class)
            ->findOneBy(array('id' => $researchGroupId));

        return $this->writeCsvResponse(
            $this->getData(['researchGroup' => $researchGroup, 'version' => 2]),
            $this->createCsvReportFileName($researchGroup->getName(), $researchGroupId)
        );
    }

    /**
     * This method gets data for the report.
     *
     * @param array $options Additional parameters needed to run the query.
     *
     * @return array  Return the data array
     */
    protected function getData(array $options): array
    {
        $datasets = $options['researchGroup']->getDatasets();
        $reportData = array();

        if (isset($options['version'])) {
            $reportData = $this->getVersionTwoReport($datasets, $options);
        } else {
            $reportData = $this->getVersionOneReport($datasets, $options);
        }
        return array_merge($this->getDefaultHeaders(), $reportData['additionalHeaders'], $reportData['labels'], $reportData['dataArray']);
    }

    /**
     * Create a CSV download filename that contains the truncated research group name and the date/timeto.
     *
     * @param string $researchGroupName The name of the Research Group which is the subject of the report.
     * @param string $researchGroupId   The ID of the Research Group which is the subject of the report.
     *
     * @return string
     */
    private function createCsvReportFileName(string $researchGroupName, string $researchGroupId)
    {
        $nowDateTimeString = date(self::REPORTFILENAMEDATETIMEFORMAT);
        $researchGroupNameSubstring = substr($researchGroupName, 0, self::MAXRESEARCHGROUPLENGTH);
        $tempFileName = $researchGroupNameSubstring . '_' . $researchGroupId
            . '_'
            . $nowDateTimeString
            . '.csv';
        return str_replace(' ', '_', $tempFileName);
    }

    /**
     * Get data for version one report.
     *
     * @param Collection $datasets  Collection of Datasets.
     * @param array      $options   Options for report.
     *
     * @return array
     */
    private function getVersionOneReport(Collection $datasets, array $options): array
    {
        $datasetCount = $this->getDatasetCount($datasets);
        //extra headers to be put in the report
        $additionalHeaders = array(
            array('RESEARCH GROUP',$options['researchGroup']->getName()),
            array('DATASET COUNT', $datasetCount['string']),
            array(parent::BLANK_LINE));

        //prepare label array
        $labels = array('labels' => array('DATASET UDI',
            'TITLE',
            'PRIMARY POINT OF CONTACT',
            'STATUS',
            'DATE APPROVED',
            'DATE REGISTERED'));

        //prepare data array
        $dataArray = array();
        if ($datasetCount['number'] > 0) {
            foreach ($datasets as $dataset) {
                $datasetStatus = $dataset->getStatus();
                //  exclude datasets that don't have an approved DIF
                if ($datasetStatus != 'NoDif') {
                    $datasetTimeStampString = 'N/A';
                    if ($dataset->getDatasetSubmission() != null &&
                        $dataset->getDatasetSubmission()->getSubmissionTimeStamp() != null) {
                        $datasetTimeStampString = $dataset->getDatasetSubmission()->getSubmissionTimeStamp()
                            ->format(self::REPORTDATETIMEFORMAT);
                    }
                    $dif = $dataset->getDif();
                    $ppoc = $dif->getPrimaryPointOfContact();
                    $ppocString = $ppoc->getLastName() . ', ' .
                        $ppoc->getFirstName();
                    $difTimeStampString = 'N/A';
                    if ($dif->getApprovedDate() != null) {
                        $difTimeStampString = $dif->getApprovedDate()->format(self::REPORTDATETIMEFORMAT);
                    }
                    $dataRow = array(
                        'udi' => $dataset->getUdi(),
                        'title' => $dataset->getTitle(),
                        'primaryPointOfContact' => $ppocString,
                        'datasetStatus' => $datasetStatus,
                        'dateIdentified' => $difTimeStampString,
                        'dateRegistered' => $datasetTimeStampString
                    );
                    $dataArray[] = $dataRow;
                }
            }
        }

        return array(
            'additionalHeaders' => $additionalHeaders,
            'labels' => $labels,
            'dataArray' => $dataArray
        );
    }

    /**
     * Getter for dataset count and dataset count string.
     *
     * @param Collection $datasets Collection of datasets.
     *
     * @return array
     */
    private function getDatasetCount(Collection $datasets): array
    {
        $datasetCount = array();
        $datasetCount['string'] = 'No datasets';

        $datasetCount['number'] = count($datasets);
        if ($datasetCount['number'] > 0) {
            $datasetCount['string'] = ' [ ' . (string) count($datasets) . ' ]';
        }
        return $datasetCount;
    }

    /**
     * Get data for version two report.
     *
     * @param Collection $datasets Collection of datasets.
     * @param array      $options  Options for report.
     *
     * @return array
     */
    private function getVersionTwoReport(Collection $datasets, array $options): array
    {
        $datasetCount = $this->getDatasetCount($datasets);
        //extra headers to be put in the report
        $additionalHeaders = array(
            array('DATASET REPORT FOR RESEARCH GROUP:',$options['researchGroup']->getName()),
            array('DATASET COUNT', $datasetCount['string']),
            array(parent::BLANK_LINE));

        //prepare label array
        $labels = array(
            'labels' => array(
                'DATASET UDI',
                'DATASET DOI',
                'TITLE',
                'PRIMARY POINT OF CONTACT',
                'STATUS',
                'RESTRICTED',
            )
        );

        //prepare data array
        $dataArray = array();
        if ($datasetCount['number'] > 0) {
            foreach ($datasets as $dataset) {
                $datasetStatus = $dataset->getStatus();
                $ppoc = $dataset->getPrimaryPointOfContact();
                $ppocString = $ppoc->getLastName() . ', ' .
                    $ppoc->getFirstName();
                $dataRow = array(
                    'udi' => $dataset->getUdi(),
                    'doi'=> $dataset->getDoi(),
                    'title' => $dataset->getTitle(),
                    'primaryPointOfContact' => $ppocString,
                    'datasetStatus' => $datasetStatus,
                    'restricted' => ($dataset->isRestricted()) ? 'YES' : 'NO',
                );
                $dataArray[] = $dataRow;
            }
        }

        return array(
            'additionalHeaders' => $additionalHeaders,
            'labels' => $labels,
            'dataArray' => $dataArray
        );
    }
}
