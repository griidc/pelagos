<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pelagos\Bundle\AppBundle\Form\LoginForm;

/**
 * This is the controller for the login form.
 */
class SecurityController extends Controller
{
    /**
     * The login action.
     *
     * @Route("/login", name="security_login")
     *
     * @return Response A Response instance.
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
                'error'         => $error,
            )
        );
    }

    /**
     * The logout action.
     *
     * @Route("/logout", name="security_logout")
     *
     * @throws \Exception This exception should not be seen.
     *
     * @return void
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached!');
    }
}
