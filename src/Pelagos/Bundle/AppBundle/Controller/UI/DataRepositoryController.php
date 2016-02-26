<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;

use Pelagos\Bundle\AppBundle\Form\DataRepositoryType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class DataRepositoryController extends UIController
{
    /**
     * The Funding Org action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/DataRepository/{id}")
     *
     * @return Response A Response instance.
     */
    public function dataRepositoryAction($id = null)
    {
        $ui = array();
        
        if ($id !== null) {
            $dataRepository = $this->entityHandler->get('Pelagos:DataRepository', $id);
            
            if (!$dataRepository instanceof \Pelagos\Entity\DataRepository) {
                throw $this->createNotFoundException('The Data Organization was not found');
            }
            
            // foreach ($dataRepository->getPersonFundingOrganizations() as $personFundingOrganization) {
                // $formView = $this
                // ->get('form.factory')
                // ->createNamed(null, PersonFundingOrganizationType::class, $personFundingOrganization)
                // ->createView();
                
                // $ui['DataRepositories'][] = $personFundingOrganization;
                // $ui['DataRepositoryForms'][$personFundingOrganization->getId()] = $formView;
            // }
        } else {
            $dataRepository = new \Pelagos\Entity\DataRepository;
        }
        
        $form = $this->get('form.factory')->createNamed(null, DataRepositoryType::class, $dataRepository);
        
        $ui['DataRepository'] = $dataRepository;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $this->entityHandler;
        
        return $this->render('PelagosAppBundle:template:DataRepository.html.twig', $ui);
    }
}
