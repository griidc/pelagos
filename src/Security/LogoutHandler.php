<?php

namespace App\Security;

use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handler for logout.
 */
class LogoutHandler implements LogoutHandlerInterface
{
    /**
     * This method triggers upon logout and clears a username-holding session cookie.
     *
     * @param Request        $request  A Symfony Request, req by interface.
     * @param Response       $response A Symfony Response, req by interface.
     * @param TokenInterface $token    A Symfony user token, req by interface.
     *
     * @return void
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $response->headers->clearCookie('GRIIDC_USERNAME');
    }
}
