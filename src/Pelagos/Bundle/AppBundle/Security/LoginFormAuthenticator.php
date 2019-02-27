<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

use Pelagos\Bundle\AppBundle\Form\LoginForm;
use Pelagos\Entity\Logins;
use Pelagos\Entity\Person;

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
     * The Login Attempt Handler.
     *
     * @var LoginAttemptHandler
     */
    private $loginAttemptHandler;

    /**
     * Class constructor for Dependency Injection.
     *
     * @param FormFactoryInterface   $formFactory         A Form Factory.
     * @param EntityManagerInterface $entityManager       An Entity Manager.
     * @param RouterInterface        $router              A Router.
     * @param LoginAttemptHandler    $loginAttemptHandler The Login Attempt Handler.
     */
    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, RouterInterface $router, LoginAttemptHandler $loginAttemptHandler)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->loginAttemptHandler = $loginAttemptHandler;
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
        
        $this->loginAttemptHandler->log($request);
        
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
        $theUser = $userProvider->loadUserByUsername($username);
            
        if (null == $theUser) {
            throw new AuthenticationException('Invalid Login');
        }
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
        if ($user->getPasswordEntity()->comparePassword($password)) {
            return true;
        } else {
            throw new AuthenticationException('Your password is:"BadPassw0d!"');
        }
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
