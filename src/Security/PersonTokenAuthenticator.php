<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Twig\Environment as TwigEnvironment;

/**
 * An authenticator for PersonTokens.
 *
 * @see AbstractFormLoginAuthenticator
 */
class PersonTokenAuthenticator extends AbstractAuthenticator
{
    /**
     * An instance of Twig.
     *
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * The user provider for the token.
     *
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * Constructor.
     *
     * @param TwigEnvironment $twig An instance of Twig.
     */
    public function __construct(TwigEnvironment $twig, PersonTokenUserProvider $userProvider)
    {
        $this->twig = $twig;
        $this->userProvider = $userProvider;
    }

    /**
     * Get the authentication credentials from the request and return them.
     *
     * @param Request $request A Request object.
     *
     * @return boolean True if person_token exists.
     */
    public function supports(Request $request): bool
    {
        return $request->query->has('person_token');
    }

    /**
     * The authenticate function for this authenticator.
     *
     * @param Request $request
     *
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        $token = $request->query->get('person_token');

        $user = $this->userProvider->loadUserByIdentifier($token);

        return new Passport(
            new UserBadge($token, function ($identifier) {
                return $this->userProvider->loadUserByIdentifier($identifier);
            }),
            new CustomCredentials(function () {
                return true;
            }, null)
        );
    }

    /**
     * Handler for when AuthenticationExceptions are thrown.
     *
     * @param Request                 $request   The Symfony response object.
     * @param AuthenticationException $exception The authentication exception thrown.
     *
     * @return Response A Symfony response object containing the authentication failure message.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
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
     * @return Response|null The response or null to continue request.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null;
    }

    /**
     * Override to control what happens when the user hits a secure page but isn't logged in yet.
     *
     * @param Request                 $request       A Symfony Request, req by interface.
     * @param AuthenticationException $authException The exception thrown.
     *
     * @throws \Exception This should not be reached.
     *
     * @return void
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new \Exception('This should not be reached, token?');
    }

    /**
     * Remember me is not supported.
     *
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }
}
