<?php

namespace App\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerEventSubscriber implements EventSubscriberInterface
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
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
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

        $toAddresses = array_map(function (Address $to) {
            return $to->toString();
        }, $message->getTo());

        $this->logger->info(
            $message->generateMessageId(),
            [
                'queued' => $event->isQueued(),
                'subject' => $message->getSubject(),
                'to' => $toAddresses,
            ]
        );
    }
}
