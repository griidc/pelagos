<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\FundingOrganizationType;
use Pelagos\Bundle\AppBundle\Form\FundingCycleType;
use Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class FundingOrganizationController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The Funding Org action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/funding-organization/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction($id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $fundingOrganization = $this->entityHandler->get('Pelagos:FundingOrganization', $id);

            if (!$fundingOrganization instanceof \Pelagos\Entity\FundingOrganization) {
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
            $fundingOrganization = new \Pelagos\Entity\FundingOrganization;
        }

        $form = $this->get('form.factory')->createNamed(null, FundingOrganizationType::class, $fundingOrganization);

        $ui['FundingOrganization'] = $fundingOrganization;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $this->entityHandler;

        return $this->render('PelagosAppBundle:template:FundingOrganization.html.twig', $ui);
    }
}
