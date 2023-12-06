<?php

namespace App\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Event Subscriber for mailing events.
 *
 * Logs e-mail events to mailer log.
 */
class MailerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(protected LoggerInterface $mailerLogger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }

    /**
     * Event handler for message event.
     */
    public function onMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if (!$message instanceof Email) {
            return;
        }

        $this->mailerLogger->info(
            $message->generateMessageId(),
            [
                'queued' => $event->isQueued(),
                'subject' => $message->getSubject(),
                'to' => array_map(function (Address $to) {
                    return $to->toString();
                }, $message->getTo()),
            ]
        );
    }
}
