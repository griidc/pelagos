<?php
namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;

use Pelagos\Bundle\AppBundle\Form\ReportResearchGroupDatasetDifType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Doctrine\ORM\Query;
use Pelagos\Entity\ResearchGroup;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 *
 * @Route("report-researchgroup-dataset-dif")
 *
 * @return Response A Symfony Response instance.
 */
class ReportResearchGroupDatasetDifController extends UIController implements OptionalReadOnlyInterface
{
    private $selectedResearchGroupId = null;
    private $associatedDatasetIds = null; // an array of Entity ids for Dataset s linked to the selecteed ResearchGroup

    private $researchGroupNames = null;

    private $fileName = 'ResearchGroup-Dataset-Dif-Quarterly-Report.csv';

    const CSV_DELIMITER = "|";
    const BLANK_LINE = "     ";


    /**
     * The default action for the DIF.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $researchGroupId = null)
    {
        //  fetch all the Research Groups
        $allResearchGroups = $this->get('pelagos.entity.handler')->getAll(ResearchGroup::class, array('name' => 'ASC'));
        //  put all the names in an array with the associated doctrine id
        $this->researchGroupNames = array();
        foreach ($allResearchGroups as $rg) {
            $this->researchGroupNames[$rg->getName()] = $rg->getId();
        }
        $selectedResearchGroupId = $researchGroupId;
        if ($selectedResearchGroupId == null) {
            //  the default is the first item value in the list
            $selectedResearchGroupId = array_values($this->researchGroupNames)[0];
        }
        //  for testing the wiring chose the 10th item
        $selectedResearchGroupId = array_values($this->researchGroupNames)[20];

        $form = $this->createFormBuilder()
            ->add('ResearchGroupSelector', ChoiceType::class, array(
                'choices' => $this->researchGroupNames, // the word 'choices' is a reserved word in this context
                'data' => $selectedResearchGroupId,
                'method' => 'POST'))
            /****************************************************
             * ->add('SelectedResearchGroup', TextType::class, [
             * 'label' => 'Your selection',
             * 'data' => array_search($selectedResearchGroupId, $this->researchGroupNames)])
             * ->add('PostOrGet', TextType::class, [
             * 'label' => 'request message type',
             * 'data' => $message_type])
             * *******************************************************/
            ->add('submit', SubmitType::class, array('label' => 'Generate Report'))
            //  ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $researchGroupId = $form->getData()['ResearchGroupSelector'];
            $rgArray = $this->get('pelagos.entity.handler')->getBy(ResearchGroup::class, array('id' => $researchGroupId));
            return $this->csvResponse($rgArray[0]);
        }

        return $this->render(
            'PelagosAppBundle:template:jvh.html.twig',
            array(
                'form' => $form->createView(),));
    }

    /**
     * Create the StreamedResponse.
     *
     * This function creates the StreamedResponse which fetches and processes the Dataset and associated DIF for the  selected Research Group.
     *
     * @param ResearchGroup $researchGroup
     * @return StreamedResponse
     */
    private function csvResponse(ResearchGroup $researchGroup)
    {

        $response = new StreamedResponse(function () use ($researchGroup) {
            $datasets = $researchGroup->getDatasets();
            $dsCount = '[ ' . (string)count($datasets) . ' ]';
            $rows = array();
            $data = array('  RESEARCH GROUP  ', $researchGroup->getName());
            $rows[] = implode(ReportResearchGroupDatasetDifController::CSV_DELIMITER, $data);
            $data = array('  DATASET COUNT  ', $dsCount);
            $rows[] = implode(ReportResearchGroupDatasetDifController::CSV_DELIMITER, $data);
            $rows[] = ReportResearchGroupDatasetDifController::BLANK_LINE;
            //  headers
            $data = array('  DATASET - UDI  ',
                '  STATUS  ',
                '  DATE SUBMITTED  ',
                '  DATASET TITLE  ',
                '  DIF STATUS  ',
                '  LAST MODIFIED DATE  ',
                '  PRIMARY POINT OF CONTACT  ',
                '  DIF TITLE  ');
            $rows[] = implode(ReportResearchGroupDatasetDifController::CSV_DELIMITER, $data);
            $rows[] = ReportResearchGroupDatasetDifController::BLANK_LINE;
            $datasetTimeStampString = 'Unknown';
            foreach ($datasets as $ds) {
                $datasetTimeStampString = 'Unknown';
                if ($ds->getDatasetSubmission() != null && $ds->getDatasetSubmission()->getSubmissionTimeStamp() != null) {
                    $datasetTimeStampString = $ds->getDatasetSubmission()->getSubmissionTimeStamp()->format('Y-m-d H:i:s');
                }
                $dif = $ds->getDif();
                $ppoc = $dif->getPrimaryPointOfContact();
                $ppocString = $ppoc->getLastName() . ', ' . $ppoc->getFirstName() . '  - ' . $ppoc->getEmailAddress();
                $difTimeStampString = 'Unknown';
                if ($dif->getModificationTimeStamp() != null) {
                    $difTimeStampString = $dif->getModificationTimeStamp()->format('Y-m-d H:i:s');
                }
                $data = array($ds->getUdi(),
                    $this->removeDelimiterFromString($ds->getWorkflowStatusString()),
                    $this->removeDelimiterFromString($datasetTimeStampString),
                    $this->removeDelimiterFromString($ds->getTitle()),
                    $this->removeDelimiterFromString($dif->getStatusString()),
                    $this->removeDelimiterFromString($difTimeStampString),
                    $this->removeDelimiterFromString($ppocString),
                    $this->removeDelimiterFromString($dif->getTitle()));
                $rows[] = implode(ReportResearchGroupDatasetDifController::CSV_DELIMITER, $data);
            }

            echo implode("\n", $rows);
        });

        $response->headers->set('Content-Disposition', 'attachment; filename=' . $this->fileName);
        return $response;
    }

    /**
     *
     * @param $string
     * @return String
     */
    private function removeDelimiterFromString($string)
    {
        return str_replace(ReportResearchGroupDatasetDifController::CSV_DELIMITER, ",", $string);
    }
}