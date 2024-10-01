<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => 'onLogout'];
    }

    /**
     * Handles logout event.
     *
     * (Clears the cookies)
     */
    public function onLogout(LogoutEvent $event): void
    {
        $response = $event->getResponse();

        if (!empty($response)) {
            $response->headers->clearCookie('GRIIDC_USERNAME');
        }
    }
}