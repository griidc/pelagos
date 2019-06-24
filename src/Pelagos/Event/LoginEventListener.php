<?php
namespace Pelagos\Event;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Event listener class for Login events.
 */
class LoginEventListener extends EventListener
{
    /**
     * The Symfony token storage object.
     *
     * @var mixedtokenStorage Symfony's token object.
     */
    protected $tokenStorage;

   /**
    * Constructor for dependency injection of TokenStorage object.
    *
    * @param TokenStorage $tokenStorage The tokenStorage Object.
    */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Method that fires upon successful login and sets a cookie with username.
     *
     * @param InteractiveLoginEvent $event The event for successful login.
     *
     * @return void
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($event->getAuthenticationToken()->isAuthenticated()) {
            $user = $event->getAuthenticationToken()->getUser();
            setcookie('GRIIDC_USERNAME', $user->getUsername(), 0, '/');
        }
    }
}
