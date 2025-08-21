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
use Symfony\Component\Form\FormFactoryInterface;
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
     * @param EntityHandler $entityHandler The enitity handler.
     * @param integer       $id            The id of the entity to retrieve.
     *
     * @throws NotFoundHttpException When person was not found.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/person/{id}', name: 'pelagos_app_ui_person_default')]
    public function defaultAction(EntityHandler $entityHandler, FormFactoryInterface $formFactory, int $id = null)
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
                    $formView = $formFactory
                    ->createNamed('', PersonResearchGroupType::class, $personResearchGroup)
                    ->createView();

                    $ui['PersonResearchGroups'][] = $personResearchGroup;
                    $ui['PersonResearchGroupForms'][$personResearchGroup->getId()] = $formView;
                    $ui['PersonResearchGroupEditLabel'][$personResearchGroup->getId()]
                        = new EntityProperty($personResearchGroup, 'label');
                }
                foreach ($person->getPersonFundingOrganizations() as $personFundingOrganization) {
                    $formView = $formFactory
                    ->createNamed('', PersonFundingOrganizationType::class, $personFundingOrganization)
                    ->createView();

                    $ui['PersonFundingOrganizations'][] = $personFundingOrganization;
                    $ui['PersonFundingOrganizationForms'][$personFundingOrganization->getId()] = $formView;
                }
            } else {
                throw new NotFoundHttpException('The person with id of ' . $id . ' could not be found.');
            }
        } else {
            $person = new \App\Entity\Person();
        }

        $form = $formFactory->createNamed('', PersonType::class, $person);

        $ui['Person'] = $person;
        $ui['form'] = $form->createView();

        return $this->render('template/Person.html.twig', $ui);
    }
}
