<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pelagos\Bundle\AppBundle\Form\LoginForm;

class SecurityController extends Controller
{
     /**
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        $form = $this->get('form.factory')->createNamed(null, LoginForm::class, [
            '_username' => $lastUsername,
        ]);
        
        return $this->render(
            'PelagosAppBundle:Security:login.html.twig',
            array(
                'form' => $form->createView(),
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }
    
    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached!');  
    }
    
}