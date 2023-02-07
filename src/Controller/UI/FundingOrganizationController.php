<?php

namespace App\Controller\UI;

use App\Entity\FundingOrganization;
use App\Form\FundingOrganizationType;
use App\Form\FundingCycleType;
use App\Form\PersonFundingOrganizationType;
use App\Handler\EntityHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class FundingOrganizationController extends AbstractController
{
    /**
     * The Funding Org action.
     *
     * @param EntityHandler        $entityHandler The entity handler.
     * @param FormFactoryInterface $formFactory   The form factory.
     * @param integer              $id            The id of the entity to retrieve.
     *
     * @throws NotFoundHttpException When the funding organization is not found.
     *
     * @Route("/funding-organization/{id}", name="pelagos_app_ui_fundingorganization_default")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(EntityHandler $entityHandler, FormFactoryInterface $formFactory, int $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $fundingOrganization = $entityHandler->get(FundingOrganization::class, $id);

            if (!$fundingOrganization instanceof \App\Entity\FundingOrganization) {
                throw new NotFoundHttpException('The Funding Organization was not found');
            }

            foreach ($fundingOrganization->getPersonFundingOrganizations() as $personFundingOrganization) {
                $formView = $formFactory
                ->createNamed('', PersonFundingOrganizationType::class, $personFundingOrganization)
                ->createView();

                $ui['PersonFundingOrganizations'][] = $personFundingOrganization;
                $ui['PersonFundingOrganizationForms'][$personFundingOrganization->getId()] = $formView;
            }

            foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
                $formView = $formFactory
                ->createNamed('', FundingCycleType::class, $fundingCycle)
                ->createView();

                $ui['FundingCycles'][] = $fundingCycle;
                $ui['FundingCycleForms'][$fundingCycle->getId()] = $formView;
            }
        } else {
            $fundingOrganization = new \App\Entity\FundingOrganization();
        }

        $form = $formFactory->createNamed('', FundingOrganizationType::class, $fundingOrganization);

        $ui['FundingOrganization'] = $fundingOrganization;
        $ui['form'] = $form->createView();

        return $this->render('template/FundingOrganization.html.twig', $ui);
    }
}
