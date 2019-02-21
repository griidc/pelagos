<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

use Pelagos\Bundle\AppBundle\Form\LoginForm;

/**
 * The login form authenticator.
 *
 * @see AbstractFormLoginAuthenticator
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    /**
     * An instance of FormFactory.
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * An instance of a Doctrine EntityManager class.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * An instance of Router.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * Class constructor for Dependency Injection.
     *
     * @param FormFactoryInterface $formFactory   A Form Factory.
     * @param EntityManager        $entityManager An Entity Manager.
     * @param RouterInterface      $router        A Router.
     */
    public function __construct(FormFactoryInterface $formFactory, EntityManager $entityManager, RouterInterface $router)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }
    
    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return bool True if this a login request.
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'security_login' && $request->isMethod('POST');
    }

    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return array Return the credentials array.
     */
    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->createNamed(null, LoginForm::class);
        $form->handleRequest($request);

        $data = $form->getData();

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['_username']
        );
 
        return $data;
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * @param array                 $credentials  Credentials Array.
     * @param UserProviderInterface $userProvider A User Provider.
     *
     * @return UserInterface Return the user.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];
        $theUser = $this->entityManager->getRepository('Pelagos:Account')
            ->findOneBy(['userId' => $username]);
        return $theUser;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * @param array         $credentials Credentials Array.
     * @param UserInterface $user        The user.
     *
     * @return bool True if the credentials are valid.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['_password'];
        return $user->getPasswordEntity()->comparePassword($password);
    }

    /**
     * Return the URL to the login page.
     *
     * @return string The login page route.
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    /**
     * Return the URL to the home page.
     *
     * @return string The home page route.
     */
    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('pelagos_homepage');
    }
}
