<?php
namespace Pelagos\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Listener for logout events.
 */
class LogoutListener implements LogoutHandlerInterface
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
        setcookie('GRIIDC_USERNAME', '', 0, '/');
    }
}
