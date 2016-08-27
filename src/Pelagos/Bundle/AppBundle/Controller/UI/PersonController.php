<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;

use Pelagos\Bundle\AppBundle\Form\PersonType;
use Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType;
use Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType;
use Pelagos\Entity\Person;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonController extends UIController
{
    /**
     * The Person Research Group action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @Route("/person/{id}")
     *
     * @throws NotFoundHttpException If Person could not be found having specified id.
     *
     * @return Response A Response instance.
     */
    public function defaultAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');

        $ui = array();

        if (isset($id)) {
            $person = $entityHandler->get('Pelagos:Person', $id);
            if ($person instanceof Person) {

                foreach ($person->getPersonResearchGroups() as $personResearchGroup) {
                    $formView = $this
                    ->get('form.factory')
                    ->createNamed(null, PersonResearchGroupType::class, $personResearchGroup)
                    ->createView();

                    $ui['PersonResearchGroups'][] = $personResearchGroup;
                    $ui['PersonResearchGroupForms'][$personResearchGroup->getId()] = $formView;
                    $ui['PersonResearchGroupEditLabel'][$personResearchGroup->getId()]
                        = new EntityProperty($personResearchGroup, 'label');
                }

                foreach ($person->getPersonFundingOrganizations() as $personFundingOrganization) {
                    $formView = $this
                    ->get('form.factory')
                    ->createNamed(null, PersonFundingOrganizationType::class, $personFundingOrganization)
                    ->createView();

                    $ui['PersonFundingOrganizations'][] = $personFundingOrganization;
                    $ui['PersonFundingOrganizationForms'][$personFundingOrganization->getId()] = $formView;
                }
            } else {
                throw new NotFoundHttpException('The person with id of ' . $id . ' could not be found.');
            }
        } else {
            $person = new \Pelagos\Entity\Person;
        }

        $form = $this->get('form.factory')->createNamed(null, PersonType::class, $person);

        $ui['Person'] = $person;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;

        return $this->render('PelagosAppBundle:template:Person.html.twig', $ui);
    }
}
