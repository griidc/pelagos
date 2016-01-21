<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * The default controller for the Pelagos App Bundle.
 */
use Pelagos\Entity\ResearchGroup;
 
 class DefaultController extends Controller
{
    /**
     * The index action.
     *
     * @return Response A Response instance.
     */
    public function indexAction()
    {
       
        $rg = new ResearchGroup();
        $rg->setName('Blaa');


       	//var_dump($user);

        if (!$this->isGranted('CAN_CREATE', $rg)) {
            throw $this->createAccessDeniedException('NO!');
        }
        
        return $this->render('PelagosAppBundle:Default:index.html.twig');
    }
}
