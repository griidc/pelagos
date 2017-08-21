<?php

namespace Pelagos\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * A generic event dispatcher.
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
     * Dispatch a generic event.
     *
     * @param mixed $subject The primary subject the event is for.
     * @param mixed $data    The additional data pertaining to the event.
     *
     * @return void
     */
    public function dispatch($subject, $data)
    {
        $eventName = $data['eventName'];
        $this->eventDispatcher->dispatch(
            'pelagos.entity.' . $subject->getUnderscoredName() . '.' . $eventName,
            new GenericEvent($data)
        );
    }
}
