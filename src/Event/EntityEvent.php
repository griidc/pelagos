<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Entity\Entity;

/**
 * Class for Entity events.
 */
class EntityEvent extends Event
{
    /**
     * The Entity this event is for.
     *
     * @var Entity
     */
    protected $entity;

    /**
     * Constructor.
     *
     * @param Entity $entity The Entity this event is for.
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Gets the Entity this event is for.
     *
     * @return Entity The Entity this event is for.
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
