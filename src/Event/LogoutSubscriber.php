<?php

namespace App\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $accountLogger;

    public function __construct(LoggerInterface $accountLogger)
    {
        $this->accountLogger = $accountLogger;
    }

    /**
     * Enumerated list of subscribed events.
     */
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
        $request = $event->getRequest();

        $username = $request->cookies->get('GRIIDC_USERNAME', '');

        $loggingContext = [
            'ipAddress' => $request->getClientIp(),
            'userName' => $username
        ];

        $this->accountLogger->info($username . ' logged out.', $loggingContext);

        if (!empty($response)) {
            $response->headers->clearCookie('GRIIDC_USERNAME');
        }
    }
}
