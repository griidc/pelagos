<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;

use Pelagos\Bundle\AppBundle\Form\ResearchGroupType;
use Pelagos\Bundle\AppBundle\Form\PersonResearchGroupType;
use Pelagos\Bundle\AppBundle\Form\PersonFundingOrganizationType;
use Pelagos\Bundle\AppBundle\Form\FundingOrganizationType;
use Pelagos\Bundle\AppBundle\Form\FundingCycleType;
use Pelagos\Bundle\AppBundle\Form\PersonType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * The default controller for the Pelagos UI App Bundle.
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
                $ui['PersonResearchGroupEditLabel'][$personResearchGroup->getId()] = new EntityProperty($personResearchGroup, 'label');
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
     * @param Request $request The HTTP request.
     * @param string  $id      The id of the entity to retrieve.
     *
     * @throws BadRequestHttpException When the Research Group ID is not provided.
     *
     * @Route("/PersonResearchGroup/{id}")
     *
     * @return Response A Response instance.
     */
    public function personResearchGroupAction(Request $request, $id = null)
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

    /**
     * The Person Research Group action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @Route("/Person/{id}")
     *
     * @return Response A Response instance.
     */
    public function personAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');

        $ui = array();

        if (isset($id)) {
            $person = $entityHandler->get('Pelagos:Person', $id);

            foreach ($person->getPersonResearchGroups() as $personResearchGroup) {
                $formView = $this
                    ->get('form.factory')
                    ->createNamed(null, PersonResearchGroupType::class, $personResearchGroup)
                    ->createView();

                $ui['PersonResearchGroups'][] = $personResearchGroup;
                $ui['PersonResearchGroupForms'][$personResearchGroup->getId()] = $formView;
                $ui['PersonResearchGroupEditLabel'][$personResearchGroup->getId()] = new EntityProperty($personResearchGroup, 'label');
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
            $person = new \Pelagos\Entity\Person;
        }

        $form = $this->get('form.factory')->createNamed(null, PersonType::class, $person);

        $ui['Person'] = $person;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;

        return $this->render('PelagosAppBundle:template:Person.html.twig', $ui);
    }
    
    /**
     * The Funding Org action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/FundingOrganization/{id}")
     *
     * @return Response A Response instance.
     */
    public function fundingOrganizationAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        
        $ui = array();
        
        if ($id !== null) {
            $fundingOrganization = $entityHandler->get('Pelagos:FundingOrganization', $id);
            
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
        $ui['entityService'] = $entityHandler;
        
        return $this->render('PelagosAppBundle:template:FundingOrganization.html.twig', $ui);
    }
    
    /**
     * The Funding Cycle action.
        *
     * @param string $id The id of the entity to retrieve.
        *
     * @throws NotFoundException When the Funding Organization is not found.
        *
     * @Route("/FundingCycle/{id}")
        *
     * @return Response A Response instance.
     */
    public function fundingCycleAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        
        $ui = array();
        
        if ($id !== null) {
            $fundingCycle = $entityHandler->get('Pelagos:FundingCycle', $id);
            
            if (!$fundingCycle instanceof \Pelagos\Entity\FundingCycle) {
                throw $this->createNotFoundException('The Funding Cycle was not found');
            }
            
        } else {
            $fundingCycle = new \Pelagos\Entity\FundingCycle;
        }
        
        $form = $this->get('form.factory')->createNamed(null, FundingCycleType::class, $fundingCycle);
        
        $ui['FundingCycle'] = $fundingCycle;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;
        
        return $this->render('PelagosAppBundle:template:FundingCycle.html.twig', $ui);
    }
    
    /**
     * The Person Funding Organization action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/PersonFundingOrganization/{id}")
     *
     * @return Response A Response instance.
     */
    public function personFundingOrganizationAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        
        $ui = array();
        
        if ($id !== null) {
            $personFundingOrganization = $entityHandler->get('Pelagos:PersonFundingOrganization', $id);
            
            if (!$personFundingOrganization instanceof \Pelagos\Entity\PersonFundingOrganization) {
                throw $this->createNotFoundException('The Person Funding Organization was not found');
            }
            
        } else {
            $personFundingOrganization = new \Pelagos\Entity\PersonFundingOrganization;
        }
        
        $form = $this->get('form.factory')->createNamed(null, PersonFundingOrganizationType::class, $personFundingOrganization);
        
        $ui['PersonFundingOrganization'] = $personFundingOrganization;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $entityHandler;
        
        return $this->render('PelagosAppBundle:template:PersonFundingOrganization.html.twig', $ui);
    }
}
