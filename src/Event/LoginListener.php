<?php

namespace App\Event;

use App\Form\LoginForm;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LoginListener implements EventSubscriberInterface
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
            LogoutEvent::class => 'onLogout',
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
            $username = 'Token User: $' . $request->get('person_token');
        } else {
            $username = $this->getCredentials($request)['_username'];
        }

        $loggingContext = [
            'ipAddress' => $request->getClientIp(),
            'userName' => $username,
        ];

        $this->logger->info('(listener) Login success.', $loggingContext);
    }

    /**
     * Logs logout events.
     */
    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        $username = $request->server->get('USERNAME');

        $loggingContext = [
            'ipAddress' => $request->getClientIp(),
            'userName' => $username
        ];

        $this->logger->info('(listener) User logged out.', $loggingContext);
    }

    /**
     * Get the authentication credentials from the request and return them.
     */
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();

        $loggingContext = [
            'ipAddress' => $request->getClientIp(),
            'userName' => $this->getCredentials($request)['_username'],
        ];

        $this->logger->info('(listener) Login failure.', $loggingContext);
    }

    /**
     * Digs the login out of the request object.
     */
    private function getCredentials(Request $request): mixed
    {
        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);

        return $form->getData();
    }
}
