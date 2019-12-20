<?php

namespace App\Util;

use Symfony\Bridge\Monolog\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;

class MailerLoggerUtil implements Swift_Events_SendListener
{
    protected $logger;

    /**
     * MailerLoggerUtil constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface  $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    : void
    {
        // ...
    }

    /**
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    : void
    {
        $level   = $this->getLogLevel($evt);
        $message = $evt->getMessage();

        $this->logger->log(
            $level,
            $message->getSubject().' - '.$message->getId(),
            [
                'result'  => $evt->getResult(),
                'subject' => $message->getSubject(),
                'to'      => $message->getTo(),
            ]
        );
    }

    /**
     * @param Swift_Events_SendEvent $evt
     *
     * @return string
     */
    private function getLogLevel(Swift_Events_SendEvent $evt)
    : string
    {
        switch ($evt->getResult()) {
            // Sending has yet to occur
            case Swift_Events_SendEvent::RESULT_PENDING:
                return LogLevel::DEBUG;

            // Email is spooled, ready to be sent
            case Swift_Events_SendEvent::RESULT_SPOOLED:
                return LogLevel::DEBUG;

            // Sending failed
            default:
            case Swift_Events_SendEvent::RESULT_FAILED:
                return LogLevel::CRITICAL;

            // Sending worked, but there were some failures
            case Swift_Events_SendEvent::RESULT_TENTATIVE:
                return LogLevel::ERROR;

            // Sending was successful
            case Swift_Events_SendEvent::RESULT_SUCCESS:
                return LogLevel::INFO;
        }
    }
}
