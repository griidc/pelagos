<?php

namespace App\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    /**
     * An instance of FormFactory.
     *
     * @var FormFactoryInterface
     */
    private $formFactory;


    private LoggerInterface $logger;

    public function __construct(LoggerInterface $accountLogger, FormFactoryInterface $formFactory)
    {
        $this->logger = $accountLogger;
        $this->formFactory = $formFactory;
    }

    /**
     * Enumerated list of subscribed events.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure'
        ];
    }

    /**
     * Logs successful logins.
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->get('person_token')) {
            $username = 'Token User: ' . $request->get('person_token');
        } else {
            $username = $request->request->all()['login_form']['_username'];
        }

        $loggingContext = [
            'ipAddress' => $request->getClientIp(),
            'userName' => $username,
        ];

        $this->logger->info($username . ' logged in.', $loggingContext);
    }

    /**
     * Get the authentication credentials from the request and return them.
     */
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();

        $loggingContext = [
            'ipAddress' => $request->getClientIp(),
            'userName' => $request->request->all()['login_form']['_username'] ?? 'unknown',
            'errorMessage' => $event->getException()->getMessage(),
        ];

        $this->logger->info('(listener) Login failure.', $loggingContext);
    }
}
