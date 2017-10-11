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
     * @param Request $request         Message information for this Request.
     * @param integer $researchGroupId The identifier for the Research Group subject of the report.
     *
     * @Route("")
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    public function defaultAction(Request $request, $researchGroupId = null)
    {
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

            $datasetCountString = 'No datasets';
            $datasets = $rgArray[0]->getDatasets();
            $dsCount = count($datasets);
            if ($dsCount > 0) {
                $datasetCountString = ' [ ' . (string) count($datasets) . ' ]';
            }

            return $this->writeCsvResponse(
                array('  DATASET UDI  ',
                    '  TITLE  ',
                    '  PRIMARY POINT OF CONTACT   ',
                    '  STATUS  ',
                    '  DATE IDENTIFIED  ',
                    '  DATE REGISTERED  '),
                $this->queryData(array('researchGroup' => $rgArray[0], 'datasetCount' => $dsCount)),
                $this->createCsvReportFileName($rgArray[0]->getName(), $researchGroupId),
                [
                    '  RESEARCH GROUP  ' => $rgArray[0]->getName(),
                    '  DATASET COUNT  ' => $datasetCountString
                ]
            );
        }

        return $this->render(
            'PelagosAppBundle:template:ReportResearchGroupDatasetStatus.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * This implements the abstract method from ReportController to get the data.
     *
     * @param array|NULL $options Additional parameters needed to run the query.
     *
     * @return array  Return an indexed array.
     */
    protected function queryData(array $options = null)
    {
        $datasets = $options['researchGroup']->getDatasets();
        $rows = array();
        if ($options['datasetCount'] > 0) {
            foreach ($datasets as $ds) {
                $datasetStatus = $ds->getStatus();
                //  exclude datasets that don't have an approved DIF
                if ($datasetStatus != 'NoDif') {
                    $datasetTimeStampString = 'N/A';
                    if ($ds->getDatasetSubmission() != null &&
                        $ds->getDatasetSubmission()->getSubmissionTimeStamp() != null) {
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
                        'udi' => $ds->getUdi(),
                        'title' => $ds->getTitle(),
                        'primaryPointOfContact' => $ppocString,
                        'datasetStatus' => $datasetStatus,
                        'dateIdentified' => $difTimeStampString,
                        'dateRegistered' => $datasetTimeStampString
                    );
                    $rows[] = $data;
                }
            }
        }
        return $rows;
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
}
