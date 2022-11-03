<?php

namespace App\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Email;

class MailerSubscriber implements EventSubscriberInterface
{
    /**
      * A Monolog logger.
      *
      * @var LoggerInterface
      */
      protected $logger;

    /**
     * Mailer Logger Subscriber constructor.
     *
     * @param LoggerInterface $mailerLogger The Logger Interface.
     */
    public function __construct(LoggerInterface $mailerLogger)
    {
        $this->logger = $mailerLogger;
    }

    /**
     * Subscribe to the event.
     *
     * @return void
     */
    public static function getSubscribedEvents()
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }

    /**
     * Event handler for message event.
     *
     * @param MessageEvent $event
     *
     * @return void
     */
    public function onMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if (!$message instanceof Email) {
            return;
        }

        $this->logger->info(
            $message->getSubject(),
            [
                'to'      => $message->getTo(),
            ]
        );
    }
}
