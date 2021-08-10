<?php

namespace App\Security;

use App\Entity\Account;
use App\Repository\PersonTokenRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * An authenticator for PersonTokens.
 *
 * @see AbstractFormLoginAuthenticator
 */
class PersonTokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * An instance of Twig.
     *
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Constructor.
     *
     * @param \Twig_Environment $twig An instance of Twig.
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
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
        return $request->query->has('person_token');
    }

    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return string Return the credential person token.
     */
    public function getCredentials(Request $request)
    {
        return $request->query->get('person_token');
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * @param string                $credentials  Person token credential.
     * @param UserProviderInterface $userProvider A User Provider.
     *
     * @throws AuthenticationException When the token is invalid or expired.
     *
     * @return UserInterface Return the user.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * Handler for when AuthenticationExceptions are thrown.
     *
     * @param Request                 $request   The Symfony response object.
     * @param AuthenticationException $exception The authentication exception thrown.
     *
     * @return Response A Symfony response object containing the authentication failure message.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof AuthenticationCredentialsNotFoundException) {
            return new Response(
                $this->twig->render('template/InvalidToken.html.twig'),
                403
            );
        }
        if ($exception instanceof AuthenticationExpiredException) {
            return new Response(
                $this->twig->render('template/ExpiredToken.html.twig'),
                403
            );
        }
        return new Response(
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            403
        );
    }

    /**
     * Authentication success.
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
        return;
    }

    /**
     * Override to control what happens when the user hits a secure page but isn't logged in yet.
     *
     * @param Request                 $request   A Symfony Request, req by interface.
     * @param AuthenticationException $exception The exception thrown.
     *
     * @throws \Exception This should not be reached.
     *
     * @return void
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new \Exception('Not used: entry_point from other authentication is used');
    }

    /**
     * Remember me is not supported.
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
