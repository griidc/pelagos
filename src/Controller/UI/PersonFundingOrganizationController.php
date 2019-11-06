<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonFundingOrganizationController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The Person Funding Organization action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/person-funding-organization/{id}")
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
            $personFundingOrganization = $this->entityHandler->get('Pelagos:PersonFundingOrganization', $id);

            if (!$personFundingOrganization instanceof \Pelagos\Entity\PersonFundingOrganization) {
                throw $this->createNotFoundException('The Person Funding Organization was not found');
            }
        } else {
            $personFundingOrganization = new \Pelagos\Entity\PersonFundingOrganization;
        }

        $form = $this->get('form.factory')->createNamed(null, PersonFundingOrganizationType::class, $personFundingOrganization);

        $ui['PersonFundingOrganization'] = $personFundingOrganization;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $this->entityHandler;

        return $this->render('PelagosAppBundle:template:PersonFundingOrganization.html.twig', $ui);
    }
}
