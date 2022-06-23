<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Form\LoginForm;
use App\Entity\Account;
use App\Entity\LoginAttempts;
use App\Entity\Password;
use App\Entity\Person;
use App\Entity\PersonToken;

/**
 * The login form authenticator.
 *
 * @see AbstractFormLoginAuthenticator
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

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
     * String describing max PW age.
     *
     * @var string maximumPasswordAge
     */
    protected $maximumPasswordAge;

    /**
     * Class constructor for Dependency Injection.
     *
     * @param FormFactoryInterface   $formFactory        A Form Factory.
     * @param EntityManagerInterface $entityManager      An Entity Manager.
     * @param RouterInterface        $router             A Router.
     * @param LoggerInterface        $logger             A Monolog logger.
     * @param string|null            $maximumPasswordAge The max age for password, 0 or null means never expires.
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        LoggerInterface $logger,
        ?string $maximumPasswordAge
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->logger = $logger;
        $this->maximumPasswordAge = $maximumPasswordAge;
    }

    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return boolean True if this a login request.
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
        $form = $this->formFactory->create(LoginForm::class);
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
     * @param mixed                 $credentials  Credentials Array.
     * @param UserProviderInterface $userProvider A User Provider.
     *
     * @throws AuthenticationException When login is invalid.
     *
     * @return UserInterface Return the user.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        // Try to find the user by e-mail.
        $thePerson = $this->entityManager->getRepository(Person::class)
            ->findOneBy(['emailAddress' => $username]);

        if ($thePerson instanceof Person) {
            $theUser = $thePerson->getAccount();
        } else {
            $theUser = $this->entityManager->getRepository(Account::class)
                ->findOneBy(['userId' => $username]);
        }

        if (null == $theUser) {
            throw new AuthenticationException('Invalid Credentials');
        }

        return $theUser;
    }

    /**
     * Returns true if the credentials are valid.
     *
     * @param mixed         $credentials Credentials Array.
     * @param UserInterface $user        The user.
     *
     * @throws AuthenticationException When account is locked out.
     * @throws AuthenticationException When this is a bad password.
     * @throws AuthenticationException When this is an expired password.
     *
     * @return boolean True if the credentials are valid.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // Here check to see if $user is locked out?
        if ($user->isLockedOut()) {
            throw new AuthenticationException('Too many login attempts');
        }

        $this->userAttempt($user);

        $password = $credentials['_password'];
        // Check that password is correct.
        if ($user->getPasswordEntity()->comparePassword($password)) {
            // Since password is correct, now check for expired password.
            if ($this->checkIfPasswordExpired($user->getPasswordEntity())) {
                throw new AuthenticationException('Password is expired.');
            }
            return true;
        } else {
            throw new AuthenticationException('Invalid Credentials');
        }
    }

    /**
     * Checks if a Password object is expired.
     *
     * @param password $password The Password object that may or may not be expired.
     *
     * @return boolean True if expired, false otherwise.
     */
    protected function checkIfPasswordExpired(password $password): bool
    {
        $passwordIsExpired = true;
        // If parameter is missing or set to 0, passwords do not expire.
        if (empty($this->maximumPasswordAge)) {
            $passwordIsExpired = false;
        } else {
            // Check for expired password.
            $now = new \DateTime('now');
            $expiration = $password->getModificationTimeStamp()->add(new \DateInterval($this->maximumPasswordAge));
            // If the current timestamp is past the calculated expiration timestamp, the password has expired.
            $passwordIsExpired = ($now > $expiration);
        }
        return $passwordIsExpired;
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
     * Set a cookie and return the response to the target page.
     *
     * @param Request        $request     A Symfony Request, req by interface.
     * @param TokenInterface $token       A Symfony user token, req by interface.
     * @param string         $providerKey The name of the used firewall key.
     *
     * @return Response The response or null to continue request.
     */
    // Next line to be ignored because implemented function does not have type-hint on $providerKey.
    // phpcs:ignore
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $destination = $request->query->get('destination');

        $session = $request->getSession();
        $targetPath = $this->getTargetPath($session, $providerKey);

        if (!isset($targetPath) and !empty($destination)) {
            $targetPath = $destination;
        } elseif (!isset($targetPath)) {
            $targetPath = $this->router->generate('pelagos_homepage');
        }

        $response = new RedirectResponse($targetPath);

        $cookie = Cookie::create('GRIIDC_USERNAME', $request->getSession()->get(Security::LAST_USERNAME));
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Return to login page and add destination for redirect.
     *
     * @param Request                 $request   A Symfony Request, req by interface.
     * @param AuthenticationException $exception The exception thrown.
     *
     * @throws AuthenticationException When the user is not found, and credentials is invalid.
     *
     * @return Response The response or null to continue request.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception->getMessage() === 'Password is expired.') {
            $credentials = $this->getCredentials($request);
            $username = $credentials['_username'];

            $user = $this->entityManager->getRepository(Account::class)
                ->findOneBy(['userId' => $username]);

            if (null === $user) {
                throw new AuthenticationException('Invalid Credentials');
            }

            $person = $user->getPerson();

            $personToken = $person->getToken();

            // if $person has Token, remove Token
            if ($personToken instanceof PersonToken) {
                $personToken->getPerson()->setToken(null);
                $this->entityManager->remove($personToken);
                $this->entityManager->flush();
            }

            $personToken = new PersonToken($person, 'PASSWORD_RESET', new \DateInterval('PT1H'));
            $this->entityManager->persist($personToken);
            $this->entityManager->flush();
            $url = $this->router->generate(
                'pelagos_app_ui_account_passwordexpired',
                array(
                    'person_token' => $person->getToken()->getTokenText()
                )
            );
        } else {
            $destination = $request->query->get('destination');
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
            $url = $this->router->generate(
                'security_login',
                ['destination' => $destination]
            );
        }
        return new RedirectResponse($url);
    }

    /**
     * Override to control what happens when the user hits a secure page but isn't logged in yet.
     *
     * @param Request                      $request       A Symfony Request, req by interface.
     * @param AuthenticationException|null $authException The exception thrown.
     *
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // Is this a JSON request?
        if (false !== strpos($request->getRequestFormat(), 'json')) {
            return new JsonResponse(['code' => 401, 'message' => 'Session expired! Please log in again'], Response::HTTP_UNAUTHORIZED);
        }

        return new RedirectResponse($this->getLoginUrl());
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
        $this->entityManager->flush();
    }
}
