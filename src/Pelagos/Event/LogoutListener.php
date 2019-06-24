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
     * @param Request $Request
     * @param Response $Response
     * @param TokenInterface $Token
     */
    public function logout(Request $Request, Response $Response, TokenInterface $Token)
    {
        setcookie("GRIIDC_USERNAME", '', 0, '/');
    }
}
