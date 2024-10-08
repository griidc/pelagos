<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\LoginForm;
use Symfony\Component\Form\FormFactoryInterface;

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
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/login', name: 'security_login')]
    public function loginAction(AuthenticationUtils $authenticationUtils, FormFactoryInterface $formFactory)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $formFactory->create(LoginForm::class, [
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
     *
     * @throws \Exception This exception should not be seen.
     * @return void
     */
    #[Route(path: '/logout', name: 'security_logout')]
    public function logoutAction()
    {
        throw new \Exception('this should not be reached!');
    }
}
