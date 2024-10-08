<?php

namespace App\Controller\UI;

use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Form\PersonResearchGroupType;
use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonResearchGroupController extends AbstractController
{
    /**
     * The Person Research Group action.
     *
     * @param Request       $request       The HTTP request.
     * @param EntityHandler $entityHandler The entity handler.
     * @param integer       $id            The id of the entity to retrieve.
     *
     * @throws BadRequestHttpException When the research group parameter is not set.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/person-research-group/{id}', name: 'pelagos_app_ui_personresearchgroup_default')]
    public function defaultAction(Request $request, EntityHandler $entityHandler, FormFactoryInterface $formFactory, int $id = null)
    {
        $researchGroupId = $request->query->get('ResearchGroup');

        if (!isset($researchGroupId) and !isset($id)) {
            throw new BadRequestHttpException('Research Group parameter is not set');
        }

        $ui = array();

        if (isset($id)) {
            $personResearchGroup = $entityHandler->get(PersonResearchGroup::class, $id);
        } else {
            $personResearchGroup = new \App\Entity\PersonResearchGroup();
            $personResearchGroup->setResearchGroup($entityHandler->get(ResearchGroup::class, $researchGroupId));
        }

        $form = $formFactory->createNamed('', PersonResearchGroupType::class, $personResearchGroup);

        $ui['PersonResearchGroup'] = $personResearchGroup;
        $ui['form'] = $form->createView();

        return $this->render('template/PersonResearchGroup.html.twig', $ui);
    }
}
