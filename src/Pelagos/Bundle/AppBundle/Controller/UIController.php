<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Pelagos\Bundle\AppBundle\Form\ResearchGroupType;
use Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The default controller for the Pelagos App Bundle.
 */
class UIController extends Controller
{

    /**
     * The Research Group action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @Route("/ResearchGroup/{id}")
     *
     * @return Response A Response instance.
     */
    public function researchGroupAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');

        $ui = array();
        $ui['PersonResearchGroups'] = array();

        if (isset($id)) {
            $researchGroup = $entityHandler->get('Pelagos:ResearchGroup', $id);

            foreach ($researchGroup->getPersonResearchGroups() as $personResearchGroup) {
                $form = $this
                    ->get('form.factory')
                    ->createNamed(null, PersonResearchGroupType::class, $personResearchGroup);
                $formView = $form->createView();

                $ui['PersonResearchGroups'][] = $personResearchGroup;
                $ui['PersonResearchGroupForms'][$personResearchGroup->getId()] = $formView;

            }

            $newResearchGroupPerson = new \Pelagos\Entity\PersonResearchGroup;
            $newResearchGroupPerson->setResearchGroup($researchGroup);
            $ui['newResearchGroupPerson'] = $newResearchGroupPerson;
            $ui['newResearchGroupPersonForm'] = $this
                ->get('form.factory')
                ->createNamed(null, PersonResearchGroupType::class, $ui['newResearchGroupPerson'])
                ->createView();
        } else {
            $researchGroup = new \Pelagos\Entity\ResearchGroup;
        }

        $form = $this->get('form.factory')->createNamed(null, ResearchGroupType::class, $researchGroup);
        $ui['form'] = $form->createView();
        $ui['ResearchGroup'] = $researchGroup;
        $ui['entityService'] = $entityHandler;

        return $this->render('PelagosAppBundle:template:ResearchGroup.html.twig', $ui);
    }

    /**
     * The Person Research Group action.
     *
     * @param string  $id      The id of the entity to retrieve.
     * @param Request $request The HTTP request.
     *
     * @throws BadRequestHttpException When the Research Group ID is not provided.
     *
     * @Route("/PersonResearchGroup/{id}")
     *
     * @return Response A Response instance.
     */
    public function personResearchGroupAction($id = null, Request $request = null)
    {
        $researchGroupId = $request->query->get('ResearchGroup');

        if (!isset($researchGroupId) and !isset($id)) {
            throw new BadRequestHttpException('Research Group parameter is not set');
        }

        $entityHandler = $this->get('pelagos.entity.handler');

        $ui = array();

        if (isset($id)) {
            $personResearchGroup = $entityHandler->get('Pelagos:PersonResearchGroup', $id);
        } else {
            $personResearchGroup = new \Pelagos\Entity\PersonResearchGroup;
            $personResearchGroup->setResearchGroup($entityHandler->get('Pelagos:ResearchGroup', $researchGroupId));
        }

        $form = $this->get('form.factory')->createNamed(null, PersonResearchGroupType::class, $personResearchGroup);

        $ui['PersonResearchGroup'] = $personResearchGroup;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;

        return $this->render('PelagosAppBundle:template:PersonResearchGroup.html.twig', $ui);
    }
}
