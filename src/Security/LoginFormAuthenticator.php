<?php

namespace App\Security;

use App\Entity\Account;
use App\Entity\LoginAttempts;
use App\Entity\Password;
use App\Entity\Person;
use App\Entity\PersonToken;
use App\Form\LoginForm;
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
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * The login form authenticator.
 *
 * @see AbstractFormLoginAuthenticator
 */
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
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
     * @var EntityManagerInterface
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
     * @var LoggerInterface
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
    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'security_login' && $request->isMethod('POST');
    }

    /**
     * Authenticate function for Form Authenticator.
     *
     * @param Request $request
     *
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);

        $credentials = $this->getCredentials($request);

        $this->logAttempt($request);

        $request->getSession()->set(
            SecurityRequestAttributes::LAST_USERNAME,
            $credentials['_username']
        );

        $username = $credentials['_username'];
        $password = $credentials['_password'];

        return new Passport(
            new UserBadge($username, function ($userIdentifier) {
                // Try to find the user by e-mail.
                $thePerson = $this->entityManager->getRepository(Person::class)
                    ->findOneBy(['emailAddress' => $userIdentifier]);

                if ($thePerson instanceof Person) {
                    $theUser = $thePerson->getAccount();
                } else {
                    $theUser = $this->entityManager->getRepository(Account::class)
                        ->findOneBy(['userId' => $userIdentifier]);
                }

                if (null == $theUser) {
                    throw new AuthenticationException('Invalid Credentials');
                }

                return $theUser;
            }),
            new CustomCredentials(function ($credentials, Account $user) {
                // Here check to see if $user is locked out?
                if ($user->isLockedOut()) {
                    throw new AuthenticationException('Too many login attempts');
                }

                $this->userAttempt($user);

                // Check that password is correct.
                if ($user->getPasswordEntity()->comparePassword($credentials)) {
                    // Since password is correct, now check for expired password.
                    if ($this->checkIfPasswordExpired($user->getPasswordEntity())) {
                        throw new AuthenticationException('Password is expired.');
                    }
                    return true;
                } else {
                    throw new AuthenticationException('Invalid Credentials');
                }
            }, $password)
        );
    }

    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return mixed Return the credentials array.
     */
    public function getCredentials(Request $request)
    {
        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);

        return $form->getData();
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
    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('security_login');
    }

    /**
     * Set a cookie and return the response to the target page.
     *
     * @param Request        $request      A Symfony Request, req by interface.
     * @param TokenInterface $token        A Symfony user token, req by interface.
     * @param string         $firewallName The name of the used firewall key.
     *
     * @return Response The response or null to continue request.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $destination = $request->query->get('destination');

        $session = $request->getSession();
        $targetPath = $this->getTargetPath($session, $firewallName);

        if (!isset($targetPath) and !empty($destination)) {
            $targetPath = $destination;
        } elseif (!isset($targetPath)) {
            $targetPath = $this->router->generate('pelagos_homepage');
        }

        $response = new RedirectResponse($targetPath);

        $cookie = Cookie::create('GRIIDC_USERNAME', $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME));
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
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
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
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
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
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        // Is this a JSON request?
        if (false !== strpos($request->getRequestFormat(), 'json')) {
            return new JsonResponse(['code' => 401, 'message' => 'Session expired! Please log in again'], Response::HTTP_UNAUTHORIZED);
        }

        return new RedirectResponse($this->getLoginUrl($request));
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
     * @param Account $user The home directory.
     *
     * @return void
     */
    private function userAttempt(Account $user)
    {
        $anonymousPerson = $this->entityManager->find(Person::class, -1);

        $loginAttempt = new LoginAttempts($user);
        $loginAttempt->setCreator($anonymousPerson);
        $this->entityManager->persist($loginAttempt);
        $this->entityManager->flush();
    }
}
