<?php

namespace App\Controller\UI;

use App\Handler\EntityHandler;
use App\Security\EntityProperty;

use App\Form\PersonType;
use App\Form\PersonResearchGroupType;
use App\Form\PersonFundingOrganizationType;
use App\Entity\Person;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonController extends AbstractController
{
    /**
     * The Person Research Group action.
     *
     * @param string $id The id of the entity to retrieve.
     * @param EntityHandler $entityHandler
     *
     * @return Response A Response instance.
     * @Route("/person/{id}", name="pelagos_app_ui_person_default")
     *
     */
    public function defaultAction(EntityHandler $entityHandler, $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if (isset($id)) {
            $person = $entityHandler->get(Person::class, $id);
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
            $person = new \App\Entity\Person;
        }

        $form = $this->get('form.factory')->createNamed(null, PersonType::class, $person);

        $ui['Person'] = $person;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;

        return $this->render('template/Person.html.twig', $ui);
    }
}
