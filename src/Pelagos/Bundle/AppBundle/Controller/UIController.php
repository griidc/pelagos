<?php

namespace Pelagos\Bundle\AppBundle\Controller;

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
     * @return Response A Response instance.
     * 
     * @Route("/ResearchGroup/{id}")
     */
    public function researchGroupAction($id = null)
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        
        $ui = array();
        
        if (isset($id)) {
            $ResearchGroup = $entityHandler->get("Pelagos:ResearchGroup", $id);
            
            foreach ($ResearchGroup->getPersonResearchGroups() as PersonResearchGroup) {
                
            }
            
        } else {
            $ResearchGroup = new \Pelagos\Entity\ResearchGroup;
        }
        
        $ui['ResearchGroup'] = $ResearchGroup;
        
        $ui['entityService'] = $entityHandler;
        
        return $this->render('PelagosAppBundle:template:ResearchGroup.html.twig', $ui);
    }
    
    /**
     * The person Research Group action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @return Response A Response instance.
     * 
     * @Route("/PersonResearchGroup/{id}")
     */
    public function personResearchGroupAction($id = null, Request $request)
    {
        $researchGroupId = $request->query->get('ResearchGroup');
        
        if (!isset($researchGroupId) and !isset($id)) {
            throw new BadRequestHttpException('Research Group parameter is not set');
        }
        
        $entityHandler = $this->get('pelagos.entity.handler');

        $ui = array();

        if (isset($id)) {
            $PersonResearchGroup = $entityHandler->get("Pelagos:PersonResearchGroup", $id);
        } else {
            $PersonResearchGroup = new \Pelagos\Entity\PersonResearchGroup;
            $PersonResearchGroup->setResearchGroup($entityHandler->get("Pelagos:ResearchGroup", $researchGroupId));
        }
        
        $form = $this->get('form.factory')->createNamed(null, PersonResearchGroupType::class, $PersonResearchGroup);

        $ui['PersonResearchGroup'] = $PersonResearchGroup;
        $ui['form'] = $form->createView();

        $ui['entityService'] = $entityHandler;

        return $this->render('PelagosAppBundle:template:PersonResearchGroup.html.twig', $ui);
    }
}
