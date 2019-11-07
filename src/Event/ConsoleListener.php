<?php

namespace Pelagos\Event;

use PhpAmqpLib\Exception\AMQPTimeoutException;

use Symfony\Component\Console\Event\ConsoleExceptionEvent;

/**
 * A listener for console events.
 */
class ConsoleListener
{
    /**
     * Method to be called when a console exception occurs.
     *
     * Just quit when a rabbitmq consumer throws an AMQPTimeoutException.
     *
     * @param ConsoleExceptionEvent $event The exception event.
     *
     * @return void
     */
    public function onException(ConsoleExceptionEvent $event)
    {
        if (get_class($event->getException()) === AMQPTimeoutException::class
            and $event->getCommand()->getName() === 'rabbitmq:consumer') {
            exit;
        }
    }
}
