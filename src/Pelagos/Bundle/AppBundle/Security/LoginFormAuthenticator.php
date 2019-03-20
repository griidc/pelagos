<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

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

use Pelagos\Entity\Account;
use Pelagos\Entity\LoginAttempts;
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
     * A Monolog logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Class constructor for Dependency Injection.
     *
     * @param FormFactoryInterface   $formFactory   A Form Factory.
     * @param EntityManagerInterface $entityManager An Entity Manager.
     * @param RouterInterface        $router        A Router.
     * @param LoggerInterface        $logger        A Monolog logger.
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        LoggerInterface $logger
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->logger = $logger;
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

        $this->logAttempt($request);

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
     * @throws AuthenticationException When login is invalid.
     *
     * @return UserInterface Return the user.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        $theUser = $this->entityManager->getRepository(Account::class)
            ->findOneBy(['userId' => $username]);

        if (null == $theUser) {
            throw new AuthenticationException('Invalid Credentials');
        }

        return $theUser;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * @param array         $credentials Credentials Array.
     * @param UserInterface $user        The user.
     *
     * @throws AuthenticationException When account is locked out.
     * @throws AuthenticationException When this is a bad password.
     *
     * @return bool True if the credentials are valid.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // Here check to see if $user is locked out?
        if ($user->isLockedOut()) {
            throw new AuthenticationException('Too many login attempts');
        }

        $this->userAttempt($user);

        $password = $credentials['_password'];
        if ($user->getPasswordEntity()->comparePassword($password)) {
            $this->clearAttempts($user);
            return true;
        } else {
            throw new AuthenticationException('Invalid Credentials');
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

    /**
     * Logs the attemps to log.
     *
     * @param Request $request The home directory.
     *
     * @return void
     */
    private function logAttempt(Request $request)
    {
        $loggingContext = array(
            'ipAddress' => $request->getClientIp(),
            'userName' => $request->request->get('_username'),
            'user-agent' => $request->headers->get('User-Agent'),
        );
        $this->logger->info('Login Attempt:', $loggingContext);
    }

    /**
     * Log the attempt in loginAttemps.
     *
     * @param UserInterface $user The home directory.
     *
     * @return void
     */
    private function userAttempt(UserInterface $user)
    {
        $anonymousPerson = $this->entityManager->find(Person::class, -1);

        $loginAttempt = new LoginAttempts($user);
        $loginAttempt->setCreator($anonymousPerson);
        $this->entityManager->persist($loginAttempt);
        $this->entityManager->flush($loginAttempt);
    }
}
