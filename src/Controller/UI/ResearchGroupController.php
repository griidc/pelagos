<?php

namespace App\Controller\UI;

use App\Entity\FundingOrganization;
use App\Entity\ResearchGroup;
use App\Handler\EntityHandler;
use App\Security\EntityProperty;
use App\Form\ResearchGroupType;
use App\Form\PersonResearchGroupType;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class ResearchGroupController extends AbstractController
{
    /**
     * The Research Group action.
     *
     * @param EntityHandler $entityHandler The Entity Handler.
     * @param integer       $id            The id of the entity to retrieve.
     *
     * @throws NotFoundHttpException When the research group was not found.
     *
     * @Route("/research-group/{id}", name="pelagos_app_ui_researchgroup_default")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(EntityHandler $entityHandler, int $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();
        $ui['PersonResearchGroups'] = array();

        if (isset($id)) {
            $researchGroup = $entityHandler->get(ResearchGroup::class, $id);

            if (!$researchGroup instanceof \App\Entity\ResearchGroup) {
                throw new NotFoundHttpException('The Research Group was not found!');
            }

            foreach ($researchGroup->getPersonResearchGroups() as $personResearchGroup) {
                $form = $this
                    ->get('form.factory')
                    ->createNamed(null, PersonResearchGroupType::class, $personResearchGroup);
                $formView = $form->createView();

                $ui['PersonResearchGroups'][] = $personResearchGroup;
                $ui['PersonResearchGroupForms'][$personResearchGroup->getId()] = $formView;
                $ui['PersonResearchGroupEditLabel'][$personResearchGroup->getId()]
                    = new EntityProperty($personResearchGroup, 'label');
            }

            $newResearchGroupPerson = new \App\Entity\PersonResearchGroup;
            $newResearchGroupPerson->setResearchGroup($researchGroup);
            $ui['newResearchGroupPerson'] = $newResearchGroupPerson;
            $ui['newResearchGroupPersonForm'] = $this
                ->get('form.factory')
                ->createNamed(null, PersonResearchGroupType::class, $ui['newResearchGroupPerson'])
                ->createView();
        } else {
            $researchGroup = new \App\Entity\ResearchGroup;
        }

        $form = $this->get('form.factory')->createNamed(null, ResearchGroupType::class, $researchGroup);
        $ui['form'] = $form->createView();
        $ui['ResearchGroup'] = $researchGroup;
        $ui['FundingOrganizations'] = $this->getDoctrine()->getRepository(FundingOrganization::class)->findAll();

        return $this->render('template/ResearchGroup.html.twig', $ui);
    }
}
