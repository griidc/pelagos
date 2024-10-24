<?php

namespace App\Controller\UI;

use App\Entity\FundingOrganization;
use App\Entity\ResearchGroup;
use App\Handler\EntityHandler;
use App\Security\EntityProperty;
use App\Form\ResearchGroupType;
use App\Form\PersonResearchGroupType;
use App\Repository\FundingOrganizationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
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
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/research-group/{id}', name: 'pelagos_app_ui_researchgroup_default')]
    public function defaultAction(EntityHandler $entityHandler, FundingOrganizationRepository $fundingOrganizationRepository, FormFactoryInterface $formFactory, int $id = null)
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
                $form = $formFactory
                    ->createNamed('', PersonResearchGroupType::class, $personResearchGroup);
                $formView = $form->createView();

                $ui['PersonResearchGroups'][] = $personResearchGroup;
                $ui['PersonResearchGroupForms'][$personResearchGroup->getId()] = $formView;
                $ui['PersonResearchGroupEditLabel'][$personResearchGroup->getId()]
                    = new EntityProperty($personResearchGroup, 'label');
            }

            $newResearchGroupPerson = new \App\Entity\PersonResearchGroup();
            $newResearchGroupPerson->setResearchGroup($researchGroup);
            $ui['newResearchGroupPerson'] = $newResearchGroupPerson;
            $ui['newResearchGroupPersonForm'] = $formFactory
                ->createNamed('', PersonResearchGroupType::class, $ui['newResearchGroupPerson'])
                ->createView();
        } else {
            $researchGroup = new \App\Entity\ResearchGroup();
        }

        $form = $formFactory->createNamed('', ResearchGroupType::class, $researchGroup);
        $ui['form'] = $form->createView();
        $ui['ResearchGroup'] = $researchGroup;
        $ui['FundingOrganizations'] = $fundingOrganizationRepository->findAll();

        return $this->render('template/ResearchGroup.html.twig', $ui);
    }

    /**
     * The Research Group Ladning page action.
     *
     * @param integer $id The id of the entity to retrieve.
     *
     * @throws NotFoundHttpException When the research group was not found.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/research-group/about/{id}', name: 'pelagos_app_ui_researchgroup_about', requirements: ['id' => '\d+'])]
    public function landingPageAction(int $id)
    {
        return $this->render('ResearchGroup/index.html.twig', ['researchGroupId' => $id]);
    }
}
