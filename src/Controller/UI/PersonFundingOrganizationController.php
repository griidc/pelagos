<?php

namespace App\Controller\UI;

use App\Entity\PersonFundingOrganization;
use App\Form\PersonFundingOrganizationType;
use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonFundingOrganizationController extends AbstractController
{
    /**
     * The Person Funding Organization action.
     *
     * @param EntityHandler $entityHandler The entity handler.
     * @param integer       $id            The id of the entity to retrieve.
     *
     * @throws NotFoundHttpException When the person funding organization is not found.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/person-funding-organization/{id}', name: 'pelagos_app_ui_personfundingorganization_default')]
    public function defaultAction(EntityHandler $entityHandler, FormFactoryInterface $formFactory, int $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $personFundingOrganization = $entityHandler->get(PersonFundingOrganization::class, $id);

            if (!$personFundingOrganization instanceof \App\Entity\PersonFundingOrganization) {
                throw new NotFoundHttpException('The Person Funding Organization was not found');
            }
        } else {
            $personFundingOrganization = new \App\Entity\PersonFundingOrganization();
        }

        $form = $formFactory->createNamed('', PersonFundingOrganizationType::class, $personFundingOrganization);

        $ui['PersonFundingOrganization'] = $personFundingOrganization;
        $ui['form'] = $form->createView();

        return $this->render('template/PersonFundingOrganization.html.twig', $ui);
    }
}
