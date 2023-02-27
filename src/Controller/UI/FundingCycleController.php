<?php

namespace App\Controller\UI;

use App\Entity\FundingCycle;
use App\Form\FundingCycleType;
use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class FundingCycleController extends AbstractController
{
    /**
     * The Funding Cycle action.
     *
     * @param EntityHandler        $entityHandler The entity handler.
     * @param FormFactoryInterface $formFactory   The form factory.
     * @param integer              $id            The id of the entity to retrieve.
     *
     * @throws NotFoundHttpException When fundingcycle was not found.
     *
     * @Route("/funding-cycle/{id}", name="pelagos_app_ui_fundingcycle_default")
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
            $fundingCycle = $entityHandler->get(FundingCycle::class, $id);

            if (!$fundingCycle instanceof \App\Entity\FundingCycle) {
                throw new NotFoundHttpException('The Funding Cycle was not found');
            }
        } else {
            $fundingCycle = new \App\Entity\FundingCycle();
        }

        $form = $formFactory->createNamed('', FundingCycleType::class, $fundingCycle);

        $ui['FundingCycle'] = $fundingCycle;
        $ui['form'] = $form->createView();

        return $this->render('template/FundingCycle.html.twig', $ui);
    }
}
