<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Pelagos\Entity\ResearchGroup;

/**
 * A controller for a Report of Research Groups and related Datasets.
 *
 * @Route("report-researchgroup-dataset-status")
 *
 * @return Response A Symfony Response instance.
 */
class ReportResearchGroupDatasetStatusController extends UIController implements OptionalReadOnlyInterface
{
    // The format used to print the date and time in the report
    const REPORTDATETIMEFORMAT = 'Y-m-d';

    // The format used to put the date and time in the report file name
    const REPORTFILENAMEDATETIMEFORMAT = 'Y-m-d';

    // Limit the research group name to this to keep filename length at 100.
    const MAXRESEARCHGROUPLENGTH = 46;

    // This is the delimiter used to make the file comma seperated value (CSV).
    const CSV_DELIMITER = ',';

    // A convenience for putting a blank line in the report
    const BLANK_LINE = '     ';

    /**
     * The default action.
     *
     * @param Request $request         Message information for this Request.
     * @param integer $researchGroupId The identifier for the Research Group subject of the report.
     *
     * @Route("")
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    public function defaultAction(Request $request, $researchGroupId = null)
    {
        // Added authorization check for users to view this page
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
        //  fetch all the Research Groups
        $allResearchGroups = $this->get('pelagos.entity.handler')->getAll(ResearchGroup::class, array('name' => 'ASC'));
        //  put all the names in an array with the associated doctrine id
        $researchGroupNames = array();
        foreach ($allResearchGroups as $rg) {
            $researchGroupNames[$rg->getName()] = $rg->getId();
        }
        $form = $this->createFormBuilder()
            ->add('ResearchGroupSelector', ChoiceType::class, array(
                // the word 'choices' is a reserved word in this context
                'choices' => $researchGroupNames,
                'placeholder' => '[select a Research Group]'))
            ->add('submit', SubmitType::class, array('label' => 'Generate Report'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $researchGroupId = $form->getData()['ResearchGroupSelector'];
            $rgArray = $this->get('pelagos.entity.handler')
                ->getBy(ResearchGroup::class, array('id' => $researchGroupId));
            return $this->csvResponse($rgArray[0]);
        }

        return $this->render(
            'PelagosAppBundle:template:ReportResearchGroupDatasetStatus.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Create the StreamedResponse.
     *
     * This function creates the StreamedResponse which fetches and processes the Dataset and associated DIF
     * for the selected Research Group.
     *
     * @param ResearchGroup $researchGroup A ResearchGroup obuject.
     *
     * @return StreamedResponse
     */
    private function csvResponse(ResearchGroup $researchGroup)
    {
        $response = new StreamedResponse(function () use ($researchGroup) {
            // Byte Order Marker to indicate UTF-8
            echo chr(0xEF) . chr(0xBB) . chr(0xBF);
            $now = date('Y-m-d H:i');
            $datasets = $researchGroup->getDatasets();
            $dsCount = count($datasets);
            $rows = array();
            $data = array('  THIS REPORT GENERATED ', $now);
            $rows[] = implode(self::CSV_DELIMITER, $data);
            $data = array('  RESEARCH GROUP  ', $researchGroup->getName());
            $rows[] = implode(self::CSV_DELIMITER, $data);
            $datasetCountString = 'No datasets';
            if ($dsCount > 0) {
                $datasetCountString = ' [ ' . (string) count($datasets) . ' ]';
            }
            $data = array('  DATASET COUNT  ', $datasetCountString);
            $rows[] = implode(self::CSV_DELIMITER, $data);
            $rows[] = self::BLANK_LINE;

            if ($dsCount > 0) {
                //  headers
                $data = array('  DATASET UDI  ',
                    '  TITLE  ',
                    '  PRIMARY POINT OF CONTACT   ',
                    '  STATUS  ',
                    '  DATE IDENTIFIED  ',
                    '  DATE REGISTERED  ');
                $rows[] = implode(self::CSV_DELIMITER, $data);
                $rows[] = self::BLANK_LINE;
                foreach ($datasets as $ds) {
                    $datasetStatus = $ds->getStatus();
                    //  exclude datasets that don't have an approved DIF
                    if ($datasetStatus != 'NoDif') {
                        $datasetTimeStampString = 'N/A';
                        if ($ds->getDatasetSubmission() != null &&
                            $ds->getDatasetSubmission()->getSubmissionTimeStamp() != null
                        ) {
                            $datasetTimeStampString = $ds->getDatasetSubmission()->getSubmissionTimeStamp()
                                ->format(self::REPORTDATETIMEFORMAT);
                        }
                        $dif = $ds->getDif();
                        $ppoc = $dif->getPrimaryPointOfContact();
                        $ppocString = $ppoc->getLastName() . ', ' .
                            $ppoc->getFirstName();
                        $difTimeStampString = 'N/A';
                        if ($dif->getModificationTimeStamp() != null) {
                            $difTimeStampString = $dif->getModificationTimeStamp()->format(self::REPORTDATETIMEFORMAT);
                        }
                        $data = array(
                            $ds->getUdi(),
                            $this->wrapInDoubleQuotes(str_replace('"', '""', $ds->getTitle())),
                            $this->wrapInDoubleQuotes($ppocString),
                            $this->wrapInDoubleQuotes($datasetStatus),
                            $difTimeStampString,
                            $datasetTimeStampString
                        );
                        $rows[] = implode(self::CSV_DELIMITER, $data);
                    }
                }
            }

            echo implode("\n", $rows);
        });

        $reportFileName = $this->createCsvReportFileName($researchGroup->getName(), $researchGroup->getId());
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $reportFileName . '"');
        $response->headers->set('Content-type', 'text/csv; charset=utf-8', true);
        return $response;
    }

    /**
     * Create a CSV download filename that contains the truncated research group name and the date/timeto.
     *
     * @param string $researchGroupName The name of the Research Group which is the subject of the report.
     * @param string $researchGroupId   The ID of the Research Group which is the subject of the report.
     *
     * @return string
     */
    private function createCsvReportFileName($researchGroupName, $researchGroupId)
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
     * Enclose a string in double quotes.
     *
     * @param string $text The data that may contain one or more commas.
     *
     * @return string
     */
    private function wrapInDoubleQuotes($text)
    {
        return '"' . $text . '"';
    }
}
