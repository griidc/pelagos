<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use App\Entity\Entity;

use App\Event\EntityEvent;

/**
 * An entity event dispatcher.
 */
class EntityEventDispatcher
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
     * Dispatch an Entity event.
     *
     * @param Entity $entity          The Entity the event is for.
     * @param string $entityEventName The name of the entity event.
     *
     * @return void
     */
    public function dispatch(Entity $entity, string $entityEventName)
    {
        $this->eventDispatcher->dispatch(
            'pelagos.entity.' . $entity->getUnderscoredName() . '.' . $entityEventName,
            new EntityEvent($entity)
        );
    }
}
