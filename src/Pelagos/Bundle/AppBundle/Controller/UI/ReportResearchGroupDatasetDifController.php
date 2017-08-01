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


    /**
     * The default action for the DIF.
     *
     * @param Request     $request The Symfony request object.
     *
     * @Route("")
     *
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request,  $researchGroupId = null)
    {
        // dump($_POST);

        $message_type = 'Unknown';
        if ($request->isMethod('POST')) {
            $message_type = 'POST message';
        } elseif ($request->isMethod('GET')) {
            $message_type = 'GET message ';
        }

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
                'data' => $selectedResearchGroupId))
            /****************************************************
            ->add('SelectedResearchGroup', TextType::class, [
                'label' => 'Your selection',
                'data' => array_search($selectedResearchGroupId, $this->researchGroupNames)])
            ->add('PostOrGet', TextType::class, [
                'label' => 'request message type',
                'data' => $message_type])
             * *******************************************************/
            ->add('submit', SubmitType::class, array('label' => 'Generate Report'))
            ->setMethod('POST')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            var_dump($form->getData());


        }

        return $this->render(
            'PelagosAppBundle:template:jvh.html.twig',
            array(
               'form' => $form->createView(),));
    }

    public function researchGroupSelectorListener(FormEvent $event)
    {
        $form = $event->getForm();
        $selectedData = $form->get('ResearchGroupSelector')->getData();
    }

/**********************************************************************************************************************
        $allResearchGroups = $this->get('pelagos.entity.handler')->getAll(ResearchGroup::class,array('name' => 'ASC'));
        $data = '';
        foreach ($allResearchGroups as $rg) {

            $datasets = $rg->getDatasets();
            $dsCount = count($datasets);
            $str = '<h3>' . $rg->getName() . '</h3> ';
            $data .= $str;
            $str = '<h4>' . $dsCount . ' data sets' . '</h4> ' ;
            $data .= $str;
            $str = '<ol>';
            foreach($datasets as $ds) {
                $str .= '<li>' . 'dataset - udi: ' . $ds->getUdi(). ' - ' . $ds->getTitle() . '<br>';
                $dif  = $ds->getDif();
                $str .= 'dif status: ' . $dif->getStatusString() . '<br>';
                $str .= 'dif title: ' . $dif->getTitle(). '<br>';
                $ppoc = $dif->getPrimaryPointOfContact();
                $str .= 'dif PPOC: ' .  $ppoc->getLastName(). ', ' . $ppoc->getFirstName() . '  - ' . $ppoc->getEmailAddress() . '<br>';
                $str .= '</li>';
            }
            $str .= '</ol>';
            $data .= $str;
        }



        return new Response(
            $data,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
 * * ************************************************************************************************************/

}