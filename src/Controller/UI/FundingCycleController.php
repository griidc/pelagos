<?php

namespace App\Controller\UI;

use App\Entity\FundingCycle;
use App\Form\FundingCycleType;

use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class FundingCycleController extends AbstractController
{
    /**
     * The Funding Cycle action.
     *
     * @param EntityHandler $entityHandler
     * @param string $id The id of the entity to retrieve.
     *
     * @return Response A Response instance.
     *
     * @Route("/funding-cycle/{id}", name="pelagos_app_ui_fundingcycle_default")
     *
     */
    public function defaultAction(EntityHandler $entityHandler, $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $fundingCycle = $entityHandler->get(FundingCycle::class, $id);

            if (!$fundingCycle instanceof \App\Entity\FundingCycle) {
                throw $this->createNotFoundException('The Funding Cycle was not found');
            }
        } else {
            $fundingCycle = new \App\Entity\FundingCycle;
        }

        $form = $this->get('form.factory')->createNamed(null, FundingCycleType::class, $fundingCycle);

        $ui['FundingCycle'] = $fundingCycle;
        $ui['form'] = $form->createView();

        return $this->render('template/FundingCycle.html.twig', $ui);
    }
}
