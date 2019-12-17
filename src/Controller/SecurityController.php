<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Form\LoginForm;

/**
 * This is the controller for the login form.
 */
class SecurityController extends AbstractController
{
    /**
     * The login action.
     *
     * @param AuthenticationUtils $authenticationUtils Instance of Authentication Utilities.
     *
     * @Route("/login", name="security_login")
     *
     * @return Response A Response instance.
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->get('form.factory')->createNamed(null, LoginForm::class, [
            '_username' => $lastUsername,
        ]);

        return $this->render(
            'Security/login.html.twig',
            array(
                'form'  => $form->createView(),
                'error' => $error,
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
