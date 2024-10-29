<?php

namespace App\Controller\UI;

use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Entity\FundingOrganization;
use App\Form\ReportFundingOrganizationType;
use App\Repository\FundingOrganizationRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A controller for a Report of Funding Organization details.
 */
class ReportFundingOrganizationController extends ReportController
{
    // The format used to put the date and time in the report file name
    const REPORTFILENAMEDATETIMEFORMAT = 'Y-m-d';

    /**
     * The default action.
     *
     * @param Request $request Message information for this Request.
     *
     *
     * @return Response A Symfony Response instance.
     */
    #[Route(path: '/report-funding-org', name: 'pelagos_app_ui_reportfundingorg_default')]
    public function defaultAction(Request $request, FundingOrganizationRepository $fundingOrganizationRepository, FormFactoryInterface $formFactory): Response
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        //  fetch all the Funding Organizations
        $fundingOrgs = $fundingOrganizationRepository->findAll();
        //  put all the names in an array with the associated doctrine id
        $fundingOrgNames = array();
        foreach ($fundingOrgs as $fundingOrg) {
            $fundingOrgNames[$fundingOrg->getName()] = $fundingOrg->getId();
        }
        $form = $formFactory->createNamed(
            '',
            ReportFundingOrganizationType::class,
            $fundingOrgNames
        );

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $fundingOrgId = $form->getData()['FundingOrgSelector'];
                $fundingOrg = $fundingOrganizationRepository->find($fundingOrgId);

                return $this->writeCsvResponse(
                    $this->getData($fundingOrg),
                    $this->createCsvReportFileName($fundingOrg->getName(), $fundingOrgId)
                );
            }
        }
        return $this->render(
            'Reports/ReportFundingOrg.html.twig',
            array('form' => $form->createView())
        );
    }


    /**
     * Create a CSV download filename that contains the funding org name and the date/timeto.
     *
     * @param string  $fundingOrgName The name of the Funding Organization which is the subject of the report.
     *
     * @return string
     */
    private function createCsvReportFileName(string $fundingOrgName)
    {
        $nowDateTimeString = date(self::REPORTFILENAMEDATETIMEFORMAT);

        $tempFileName = $fundingOrgName
            . '_'
            . $nowDateTimeString
            . '.csv';

        return str_replace(' ', '_', $tempFileName);
    }

    /**
     * Get funding org report data for the report
     *
     * @param FundingOrganization $fundingOrg Funding organization that report is generated on.
     *
     * @return array
     */
    private function getData(FundingOrganization $fundingOrg): array
    {
        $defaultHeaders = $this->getDefaultHeaders();

        //prepare label array
        $labels = array(
            'labels' => array(
                'FUNDING CYCLE',
                'RESEARCH GROUP',
                'IDENTIFIED',
                'SUBMITTED',
                'AVAILABLE',
                'RESTRICTED',
            )
        );
        $fundingCycles = $fundingOrg->getFundingCycles();
        //prepare data array
        $dataArray = array();
        foreach ($fundingCycles as $fundingCycle) {
            $researchGroups = $fundingCycle->getResearchGroups();
            foreach ($researchGroups as $researchGroup) {
                $datasetCount = $this->getDatasetStatusCount($researchGroup->serializeDatasets());
                $dataRow = array(
                    'fundingCycle' => $fundingCycle->getName(),
                    'researchGroup' => $researchGroup->getName(),
                    'identified' => $datasetCount['identified'],
                    'submitted' => $datasetCount['submitted'],
                    'available' => $datasetCount['available'],
                    'restricted' => $datasetCount['restricted']
                );
                $dataArray[] = $dataRow;
            }
        }

        return array_merge($defaultHeaders, $labels, $dataArray);
    }

    /**
     * Get dataset status count.
     *
     * @param array $datasets
     *
     * @return int[]
     */
    private function getDatasetStatusCount(array $datasets): array
    {
        $identified = 0;
        $submitted = 0;
        $restricted = 0;
        $available = 0;

        foreach ($datasets as $dataset) {
            $availabilityStatus = $dataset['availabilityStatus'];
            if ($availabilityStatus === DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE) {
                $identifiedStatus = $dataset['dif']['status'];
                if ($identifiedStatus === DIF::STATUS_APPROVED) {
                    $identified++;
                }
            } elseif (
                in_array($availabilityStatus, [
                DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION,
                DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL
                ])
            ) {
                $submitted++;
            } elseif (
                in_array($availabilityStatus, [
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED
                ])
            ) {
                $restricted++;
            } elseif (
                in_array($availabilityStatus, [
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE
                ])
            ) {
                $available++;
            }
        }

        return [
            'identified' => $identified,
            'submitted' => $submitted,
            'restricted' => $restricted,
            'available' => $available
        ];
    }
}
