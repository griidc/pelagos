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

    const FILENAME = 'ReportResearchGroupDatasetDifControlerTempFile.csv';

    const CSV_DELIMITER = '|';

    const BLANK_LINE = '     ';

    /**
     * The default action.
     *
     * @param Request $request         Message information for this Request.
     * @param null    $researchGroupId The identifier for the Research Group subject of the report.
     *
     * @Route("")
     *
     * @return Response|StreamedResponse A Symfony Response instance.
     */
    public function defaultAction(Request $request, $researchGroupId = null)
    {
        //  fetch all the Research Groups
        $allResearchGroups = $this->get('pelagos.entity.handler')->getAll(ResearchGroup::class, array('name' => 'ASC'));
        //  put all the names in an array with the associated doctrine id
        $researchGroupNames = array();
        foreach ($allResearchGroups as $rg) {
            $researchGroupNames[$rg->getName()] = $rg->getId();
        }
        $selectedResearchGroupId = $researchGroupId;
        if ($selectedResearchGroupId == null) {
            //  the default is the first item value in the list
            $selectedResearchGroupId = array_values($researchGroupNames)[0];
        }
        $selectedResearchGroupId = array_values($researchGroupNames)[20];

        $form = $this->createFormBuilder()
            ->add('ResearchGroupSelector', ChoiceType::class, array(
                // the word 'choices' is a reserved word in this context
                'choices' => $researchGroupNames,
                'data' => $selectedResearchGroupId,
                'method' => 'POST'))
            ->add('submit', SubmitType::class, array('label' => 'Generate Report'))
            //  ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $researchGroupId = $form->getData()['ResearchGroupSelector'];
            $rgArray = $this->get('pelagos.entity.handler')
                ->getBy(ResearchGroup::class, array('id' => $researchGroupId));
            return $this->csvResponse($rgArray[0]);
        }

        return $this->render(
            'PelagosAppBundle:template:jvh.html.twig',
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
            $now = date('Y-m-d H:i');
            $datasets = $researchGroup->getDatasets();
            $dsCount = '[ ' . (string) count($datasets) . ' ]';
            $rows = array();
            $data = array('  RESEARCH GROUP  ', $researchGroup->getName());
            $rows[] = implode(self::CSV_DELIMITER, $data);
            $data = array('  DATASET COUNT  ', $dsCount);
            $rows[] = implode(self::CSV_DELIMITER, $data);
            $data = array('  GENERATED ' , $now);
            $rows[] = implode(self::CSV_DELIMITER, $data);
            $rows[] = self::BLANK_LINE;
            //  headers
            $data = array('  DATASET - UDI  ',
                '  STATUS  ',
                '  DATE SUBMITTED  ',
                '  TITLE  ',
                '  DIF STATUS  ',
                '  LAST MODIFIED DATE  ',
                '  PRIMARY POINT OF CONTACT  ');
            $rows[] = implode(self::CSV_DELIMITER, $data);
            $rows[] = self::BLANK_LINE;
            $datasetTimeStampString = 'Unknown';
            foreach ($datasets as $ds) {
                $datasetTimeStampString = 'Unknown';
                if ($ds->getDatasetSubmission() != null &&
                    $ds->getDatasetSubmission()->getSubmissionTimeStamp() != null) {
                    $datasetTimeStampString = $ds->getDatasetSubmission()->getSubmissionTimeStamp()
                         ->format('Y-m-d H:i:s');
                }
                $dif = $ds->getDif();
                $ppoc = $dif->getPrimaryPointOfContact();
                $ppocString = $ppoc->getLastName() . ', ' . $ppoc->getFirstName() . '  - ' . $ppoc->getEmailAddress();
                $difTimeStampString = 'Unknown';
                if ($dif->getModificationTimeStamp() != null) {
                    $difTimeStampString = $dif->getModificationTimeStamp()->format('Y-m-d H:i:s');
                }
                $data = array($ds->getUdi(),
                    str_replace(self::CSV_DELIMITER, ',', $ds->getWorkflowStatusString()),
                    str_replace(self::CSV_DELIMITER, ',', $datasetTimeStampString),
                    str_replace(self::CSV_DELIMITER, ',', $ds->getTitle()),
                    str_replace(self::CSV_DELIMITER, ',', $dif->getStatusString()),
                    str_replace(self::CSV_DELIMITER, ',', $difTimeStampString),
                    str_replace(self::CSV_DELIMITER, ',', $ppocString));
                $rows[] = implode(self::CSV_DELIMITER, $data);
            }

            echo implode("\n", $rows);
        });

        $response->headers->set('Content-Disposition', 'attachment; filename=' . self::FILENAME);
        return $response;
    }
}
