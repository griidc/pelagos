<?php

namespace App\Controller\UI;

use App\Form\ReportResearchGroupDatasetStatusType;
use App\Entity\ResearchGroup;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Handler\EntityHandler;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * Report version number 1.
     */
    const REPORT_VERSION_ONE = 1;

    /*
     * Report version number 2.
     */
    const REPORT_VERSION_TWO = 2;

    /**
     * Report version number 3.
     */
    const REPORT_VERSION_THREE = 3;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * The default action.
     *
     * @param Request       $request         Message information for this Request.
     * @param EntityHandler $entityHandler   The entity handler.
     *
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    #[Route(path: '/report-researchgroup-dataset-status', name: 'pelagos_app_ui_reportresearchgroupdatasetstatus_default')]
    public function defaultAction(Request $request, EntityHandler $entityHandler, FormFactoryInterface $formFactory)
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
        $form = $formFactory->createNamed(
            '',
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
                    $this->createCsvReportFileName($researchGroup->getName(), $researchGroupId, self::REPORT_VERSION_ONE)
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
     * @param integer|null  $id              Research group id.
     *
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    #[Route(path: '/report-researchgroup/dataset-monitoring/{id}', name: 'pelagos_app_ui_reportresearchgroupdatasetstatus_datasetmonitoringreport')]
    public function datasetMonitoringReportAction(Request $request, EntityHandler $entityHandler, FormFactoryInterface $formFactory, int $id = null)
    {
        if ($id) {
            return $this->getReport($id, self::REPORT_VERSION_TWO);
        }

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
        $form = $formFactory->createNamed(
            '',
            ReportResearchGroupDatasetStatusType::class,
            $researchGroupNames
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $researchGroupId = $request->get('ResearchGroupSelector');
                return $this->getReport($researchGroupId, self::REPORT_VERSION_TWO);
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
     * @param string|null   $id              Research group id.
     *
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    #[Route(path: '/report-researchgroup/grp-report/{id}', name: 'pelagos_app_ui_reportresearchgroupdatasetstatus_grpresearchgroupreport')]
    public function getGrpResearchGroupReport(Request $request, EntityHandler $entityHandler, FormFactoryInterface $formFactory, string $id = null)
    {
        if (isset($id) and is_int($id)) {
            return $this->getReport($id, self::REPORT_VERSION_THREE);
        }

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
        $form = $formFactory->createNamed(
            '',
            ReportResearchGroupDatasetStatusType::class,
            $researchGroupNames
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $researchGroupId = $request->get('ResearchGroupSelector');
                return $this->getReport($researchGroupId, self::REPORT_VERSION_THREE);
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
     * @param string|null   $id              Research group id.
     *
     *
     * @return Response|JsonResponse A Symfony Response instance.
     */
    #[Route(path: '/report-researchgroup/detail-report/{id}', name: 'pelagos_app_ui_reportresearchgroup_detailreport')]
    public function getResearchDetailReport(Request $request, EntityHandler $entityHandler, FormFactoryInterface $formFactory, string $id = null): Response
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        if (isset($id)) {
            return $this->getResearchGrpJson($id, true);
        }

        //  fetch all the Research Groups
        $allResearchGroups = $entityHandler->getAll(ResearchGroup::class, array('name' => 'ASC'));
        //  put all the names in an array with the associated doctrine id
        $researchGroupNames = array();
        foreach ($allResearchGroups as $rg) {
            $researchGroupNames[$rg->getName()] = $rg->getId();
        }
        $form = $formFactory->createNamed(
            '',
            ReportResearchGroupDatasetStatusType::class,
            $researchGroupNames
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $researchGroupId = $request->get('ResearchGroupSelector');
                return $this->getResearchGrpJson($researchGroupId);
            }
        }

        return $this->render(
            'Reports/ReportResearchGroupDatasetStatus.html.twig',
            array(
                'form' => $form->createView(),
                'reportTitle' => 'Research Group Detail Report',
            )
        );
    }

    /**
     * Get Research Group data as JSON.
     *
     * @param integer $researchGroupId The research group to generate json for.
     *
     * @return Response
     */
    private function getResearchGrpJson(int $researchGroupId, bool $stream = false): Response
    {
        $serializer = SerializerBuilder::create()->build();

        $researchGroup = $this->entityManager->getRepository(ResearchGroup::class)
        ->findOneBy(array('id' => $researchGroupId));

        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);

        $json = $serializer->serialize(
            $researchGroup,
            'json',
            $context
        );

        $data = json_decode($json);

        // Remove un needed elements.
        unset($data->funding_cycle);
        unset($data->_links);
        unset($data->creator);
        unset($data->modifier);

        foreach ($data->person_research_groups as $personResearchGroup) {
            unset($personResearchGroup->_links);
            unset($personResearchGroup->creator);
            unset($personResearchGroup->modifier);
            unset($personResearchGroup->person->creator);
            unset($personResearchGroup->person->modifier);
            unset($personResearchGroup->role->creator);
            unset($personResearchGroup->role->modifier);
        }

        $filename = $researchGroup->getId()
            . '_' . $researchGroup->getShortName()
            . '_' . date('Ymd')
            . '.json';
        if (!$stream) {
            $header = array('Content-Disposition' => "attachment; filename=$filename;");
        } else {
            $header = array();
        }
        return new JsonResponse($data, 200, $header);
    }

    /**
     * Generate report action for Dataset Research group.
     *
     * @param integer $researchGroupId The Research Group ID.
     * @param integer $version         Report version.
     *
     * @return Response A Response instance.
     */
    private function getReport(int $researchGroupId, int $version)
    {
        $researchGroup = $this->entityManager->getRepository(ResearchGroup::class)
            ->findOneBy(array('id' => $researchGroupId));
        $researchGroupName = $researchGroup->getName();

        if ($version === self::REPORT_VERSION_THREE and $researchGroup instanceof ResearchGroup) {
            $researchGroupName = $researchGroup->getFundingCycle()->getName() . '_' .
                $this->getProjectDirectorLastName($researchGroup) . '_' .
                $researchGroup->getShortName() . '_' .
                $udi = sprintf('%s.x%03d', $researchGroup->getFundingCycle()->getUdiPrefix(), $researchGroup->getId());
        }

        return $this->writeCsvResponse(
            $this->getData(['researchGroup' => $researchGroup, 'version' => $version]),
            $this->createCsvReportFileName($researchGroupName, $researchGroupId, $version)
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
        $defaultHeaders = $this->getDefaultHeaders();
        if (isset($options['version']) and $options['version'] === self::REPORT_VERSION_TWO) {
            $reportData = $this->getVersionTwoReport($datasets, $options);
            $defaultHeaders[0] = $reportData['additionalHeaders'][0];
            array_shift($reportData['additionalHeaders']);
        } elseif (isset($options['version']) and $options['version'] === self::REPORT_VERSION_THREE) {
            $reportData = $this->getVersionThreeReport($datasets, $options);
            $defaultHeaders[0] = $reportData['additionalHeaders'][0];
            array_shift($reportData['additionalHeaders']);
        } else {
            $reportData = $this->getVersionOneReport($datasets, $options);
        }
        return array_merge($defaultHeaders, $reportData['additionalHeaders'], $reportData['labels'], $reportData['dataArray']);
    }

    /**
     * Create a CSV download filename that contains the truncated research group name and the date/timeto.
     *
     * @param string  $researchGroupName The name of the Research Group which is the subject of the report.
     * @param string  $researchGroupId   The ID of the Research Group which is the subject of the report.
     * @param integer $version           Report version number.
     *
     * @return string
     */
    private function createCsvReportFileName(string $researchGroupName, string $researchGroupId, int $version)
    {
        $nowDateTimeString = date(self::REPORTFILENAMEDATETIMEFORMAT);

        if ($version === self::REPORT_VERSION_THREE) {
            $tempFileName = $researchGroupName . '_'
            . $nowDateTimeString
            . '.csv';
        } else {
            $researchGroupNameSubstring = substr($researchGroupName, 0, self::MAXRESEARCHGROUPLENGTH);
            $tempFileName = $researchGroupNameSubstring . '_' . $researchGroupId
                . '_'
                . $nowDateTimeString
                . '.csv';
        }

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
                    if (
                        $dataset->getDatasetSubmission() != null &&
                        $dataset->getDatasetSubmission()->getSubmissionTimeStamp() != null
                    ) {
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
            array('DATASET COUNT', $datasetCount['number']),
            array(parent::BLANK_LINE));

        //prepare label array
        $labels = array(
            'labels' => array(
                'DATASET UDI',
                'DATASET DOI',
                'TITLE',
                'PRIMARY POINT OF CONTACT',
                'DATASET STATUS',
                'RESTRICTED',
            )
        );

        //prepare data array
        $dataArray = array();
        if ($datasetCount['number'] > 0) {
            foreach ($datasets as $dataset) {
                $datasetStatus = $dataset->getDatasetStatus();
                $ppoc = $dataset->getPrimaryPointOfContact();
                $ppocString = ($ppoc) ? $ppoc->getLastName() . ', ' .
                    $ppoc->getFirstName() : null;
                $dataRow = array(
                    'udi' => $dataset->getUdi(),
                    'doi' => $dataset->getDoi(),
                    'title' => $dataset->getTitle(),
                    'primaryPointOfContact' => $ppocString,
                    'datasetStatus' => $this->getDatasetStatus($dataset),
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

    /**
     * Get custom dataset status string for the version two report.
     *
     * @param Dataset $dataset An instance of dataset entity.
     *
     * @return string
     */
    private function getDatasetStatus(Dataset $dataset): string
    {
        switch (true) {
            case ($dataset->getStatus() === 'NoDif'):
                return 'Unapproved DIF';
                break;
            case ($dataset->getStatus() === 'DIF'):
                return 'Approved DIF';
                break;
            case ($dataset->getStatus() === 'In Review'):
                return 'In Review';
                break;
            case ($dataset->getStatus() === 'Back to Submitter'):
                return 'Revisions Requested';
                break;
            case (in_array($dataset->getStatus(), ['Completed', 'Completed, Restricted'])):
                return 'Completed';
                break;
            case ($dataset->getStatus() === 'Submitted'):
                return 'Submitted';
                break;
        }
    }

    /**
     * Get project directors last name.
     *
     * @param ResearchGroup $researchGroup The research group entity instance.
     *
     * @return string
     */
    private function getProjectDirectorLastName(ResearchGroup $researchGroup): string
    {
        $projectDirectorLastName = '';
        foreach ($researchGroup->getProjectDirectors() as $projectDirector) {
            if ($projectDirectorLastName) {
                $projectDirectorLastName = $projectDirectorLastName . '_' . $projectDirector->getLastName();
            } else {
                $projectDirectorLastName = $projectDirector->getLastName();
            }
        }

        return $projectDirectorLastName;
    }

    /**
     * Get data for version three report.
     *
     * @param Collection $datasets Collection of datasets.
     * @param array      $options  Options for report.
     *
     * @return array
     */
    private function getVersionThreeReport(Collection $datasets, array $options): array
    {
        $datasetCount = $this->getDatasetCount($datasets);
        //extra headers to be put in the report
        $additionalHeaders = array(
            array('DATASET REPORT FOR PROJECT:', $options['researchGroup']->getName()),
            array('PROJECT DIRECTOR', $this->getProjectDirectorName($options['researchGroup'])),
            array('GRANT AWARD', $options['researchGroup']->getFundingCycle()->getName()),
            array('DATASET COUNT', $datasetCount['number']),
            array(parent::BLANK_LINE));

        //prepare label array
        $labels = array(
            'labels' => array(
                'DATASET UDI',
                'DATASET DOI',
                'TITLE',
                'PRIMARY POINT OF CONTACT',
                'SUBMITTER',
                'LAST UPDATE',
                'DATASET STATUS',
                'RESTRICTED',
            )
        );

        //prepare data array
        $dataArray = array();
        if ($datasetCount['number'] > 0) {
            foreach ($datasets as $dataset) {
                $datasetStatus = $dataset->getDatasetStatus();
                $ppoc = $dataset->getPrimaryPointOfContact();
                $ppocString = ($ppoc) ? $ppoc->getLastName() . ', ' .
                    $ppoc->getFirstName() : null;
                $dataRow = array(
                    'udi' => $dataset->getUdi(),
                    'doi' => $dataset->getDoi(),
                    'title' => $dataset->getTitle(),
                    'primaryPointOfContact' => $ppocString,
                    'submitter' => $this->getSubmitter($dataset),
                    'lastUpdate' => $this->getLastUpdate($dataset),
                    'datasetStatus' => $this->getDatasetStatus($dataset),
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

    /**
     * Get dataset submission submitter name.
     *
     * @param Dataset $dataset
     *
     * @return string
     */
    private function getSubmitter(Dataset $dataset): string
    {
        $submitter = '';
        if ($dataset->getDatasetSubmission() instanceof DatasetSubmission) {
            $submitter = $dataset->getDatasetSubmission()->getSubmitter()->getLastName() . ', ' .
                $dataset->getDatasetSubmission()->getSubmitter()->getLastName();
        }
        return $submitter;
    }

    /**
     * Get dataset's last update date.
     *
     * @param Dataset $dataset Instance of Dataset entity.
     *
     * @return string
     */
    private function getLastUpdate(Dataset $dataset): string
    {
        $lastUpdate = 'N/A';
        if ($dataset->getDatasetSubmission() instanceof DatasetSubmission) {
            $lastUpdate = $dataset->getDatasetSubmission()->getSubmissionTimeStamp()->format('Y-m-d H:i');
        } elseif ($dataset->getDif() instanceof DIF) {
            if ($dataset->getDif()->getApprovedDate()) {
                $lastUpdate = $dataset->getDif()->getApprovedDate()->format('Y-m-d H:i');
            } else {
                $lastUpdate = $dataset->getDif()->getModificationTimeStamp()->format('Y-m-d H:i');
            }
        }

        return $lastUpdate;
    }

    /**
     * Get project director names.
     *
     * @param ResearchGroup $researchGroup Instance of Research group entity.
     *
     * @return string
     */
    private function getProjectDirectorName(ResearchGroup $researchGroup): string
    {
        $projectDirectorNames = '';
        foreach ($researchGroup->getProjectDirectors() as $projectDirector) {
            if ($projectDirectorNames) {
                $projectDirectorNames = $projectDirectorNames . ', ' .
                    $projectDirector->getLastName() . ', ' .
                    $projectDirector->getFirstName();
            } else {
                $projectDirectorNames = $projectDirector->getLastName() . ', ' .
                    $projectDirector->getFirstName();
            }
        }
        return $projectDirectorNames;
    }
}
