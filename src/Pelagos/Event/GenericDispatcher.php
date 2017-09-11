<?php

namespace Pelagos\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * A generic dispatcher.
 */
class GenericDispatcher
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
   * @param mixed  $data      The payload the event is for.
   * @param string $eventName The name of the event.
   *
   * @return void
   */
    public function dispatch($data, $eventName)
    {
        $this->eventDispatcher->dispatch(
            $eventName,
            new GenericEvent($data)
        );
    }
}
