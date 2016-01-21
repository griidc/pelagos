<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * The default controller for the Pelagos App Bundle.
 */
class DefaultController extends Controller
{
    /**
     * The index action.
     *
     * @return Response A Response instance.
     */
    public function indexAction()
    {
        echo 'here?';
        
        $user = $this->getUser();

       	var_dump($user);

        if (!$this->isGranted('CAN_CREATE', $user)) {
            throw $this->createAccessDeniedException('NO!');
        }
        
        return $this->render('PelagosAppBundle:Default:index.html.twig');
    }
}
