<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

/**
 * An authenticator for PersonTokens.
 *
 * @see SimplePreAuthenticatorInterface
 * @see AuthenticationFailureHandlerInterface
 */
class PersonTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * An instance of Twig.
     *
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Constructor that saves an instance of Twig into a class variable.
     *
     * @param \Twig_Environment $twig An instance of Twig.
     */
    public function __construct()
    {
        //$this->twig = $twig;
    }

    /**
     * Create a PreAuthenticatedToken containing the token string.
     *
     * @param Request $request     The Symfony request object.
     * @param string  $providerKey The provider key.
     *
     * @return PreAuthenticatedToken|null A new authentication token or null if token is not set.
     */
    public function createToken(Request $request, $providerKey)
    {
        $tokenString = $request->query->get('person_token');

        if (!$tokenString) {
            // Skip Person Token authentication if token is not set.
            return null;
        }

        // Return an unauthenticated token containing the token string.
        return new PreAuthenticatedToken(
            'anon.',
            $tokenString,
            $providerKey
        );
    }

    /**
     * Authenticate a Symfony authentication token.
     *
     * @param TokenInterface        $token        The token to authenticate.
     * @param UserProviderInterface $userProvider The User Provider to use for authentication.
     * @param string                $providerKey  The provider key.
     *
     * @throws \InvalidArgumentException When attemptong to user this authenticator with a
     *                                  userProvider that is not a PersonTokenUserProvider.
     *
     * @return PreAuthenticatedToken An authenticated Symfony authentication token.
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof PersonTokenUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of PersonTokenUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $tokenString = $token->getCredentials();

        $account = $userProvider->loadUserByUsername($tokenString);

        return new PreAuthenticatedToken(
            $account,
            $tokenString,
            $providerKey,
            $account->getRoles()
        );
    }

    /**
     * Check whether a token is supported by this authenticator.
     *
     * @param TokenInterface $token       The token to check.
     * @param string         $providerKey The provider key.
     *
     * @return boolean Whether the token is supported.
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
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
                $this->twig->render('PelagosAppBundle:template:InvalidToken.html.twig'),
                403
            );
        }
        if ($exception instanceof AuthenticationExpiredException) {
            return new Response(
                $this->twig->render('PelagosAppBundle:template:ExpiredToken.html.twig'),
                403
            );
        }
        return new Response(
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            403
        );
    }
}
