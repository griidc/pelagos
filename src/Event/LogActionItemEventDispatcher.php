<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A generic dispatcher.
 */
class LogActionItemEventDispatcher
{
  /**
   * The event dispatcher to use in this entity event dispatcher.
   *
   * @var EventDispatcherInterface
   */
    private $eventDispatcher;

  /**
   * Constructor.
   *
   * @param EventDispatcherInterface $eventDispatcher The event dispatcher to use.
   */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

  /**
   * Dispatch an event.
   *
   * @param array  $data      The payload the event is for.
   * @param string $eventName The name of the event.
   *
   * @return void
   */
    public function dispatch(array $data, string $eventName)
    {
        $this->eventDispatcher->dispatch(
            'pelagos.logactionitem.' . $eventName,
            new LogActionItemEvent(
                $data['actionName'],
                $data['subjectEntityName'],
                $data['subjectEntityId'],
                $data['payLoad']
            )
        );
    }
}
