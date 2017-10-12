<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;
use Pelagos\Bundle\AppBundle\Form\ResearchGroupType;
use Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class ResearchGroupController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The Research Group action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @Route("/research-group/{id}")
     *
     * @throws createNotFoundException If research group could not be found.
     *
     * @return Response A Response instance.
     */
    public function defaultAction($id = null)
    {
        // Added authorization check for users to view this page
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $ui = array();
        $ui['PersonResearchGroups'] = array();

        if (isset($id)) {
            $researchGroup = $this->entityHandler->get('Pelagos:ResearchGroup', $id);

            if (!$researchGroup instanceof \Pelagos\Entity\ResearchGroup) {
                throw $this->createNotFoundException('The Research Group was not found!');
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
        $ui['entityService'] = $this->entityHandler;

        return $this->render('PelagosAppBundle:template:ResearchGroup.html.twig', $ui);
    }
}
