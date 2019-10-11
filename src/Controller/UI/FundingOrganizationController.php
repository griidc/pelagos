<?php

namespace App\Controller\UI;

use App\Entity\FundingOrganization;
use App\Form\FundingOrganizationType;
use App\Form\FundingCycleType;
use App\Form\PersonFundingOrganizationType;

use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class FundingOrganizationController extends AbstractController
{
    /**
     * The Funding Org action.
     *
     * @param EntityHandler $entityHandler
     * @param string $id The id of the entity to retrieve.
     *
     * @return Response A Response instance.
     *
     * @Route("/funding-organization/{id}", name="pelagos_app_ui_fundingorganization_default")
     */
    public function defaultAction(EntityHandler $entityHandler, $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $fundingOrganization = $entityHandler->get(FundingOrganization::class, $id);

            if (!$fundingOrganization instanceof \App\Entity\FundingOrganization) {
                throw $this->createNotFoundException('The Funding Organization was not found');
            }

            foreach ($fundingOrganization->getPersonFundingOrganizations() as $personFundingOrganization) {
                $formView = $this
                ->get('form.factory')
                ->createNamed(null, PersonFundingOrganizationType::class, $personFundingOrganization)
                ->createView();

                $ui['PersonFundingOrganizations'][] = $personFundingOrganization;
                $ui['PersonFundingOrganizationForms'][$personFundingOrganization->getId()] = $formView;
            }

            foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
                $formView = $this
                ->get('form.factory')
                ->createNamed(null, FundingCycleType::class, $fundingCycle)
                ->createView();

                $ui['FundingCycles'][] = $fundingCycle;
                $ui['FundingCycleForms'][$fundingCycle->getId()] = $formView;
            }
        } else {
            $fundingOrganization = new \App\Entity\FundingOrganization;
        }

        $form = $this->get('form.factory')->createNamed(null, FundingOrganizationType::class, $fundingOrganization);

        $ui['FundingOrganization'] = $fundingOrganization;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;

        return $this->render('template/FundingOrganization.html.twig', $ui);
    }
}
