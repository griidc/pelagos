<?php

namespace App\Security;

use App\Entity\Entity;

/**
 * A class to represent a property of a specific entity object.
 */
class EntityProperty
{
    /**
     * A Pelagos Entity.
     *
     * @var Entity
     */
    protected $entity;

    /**
     * The name of a property of $entity.
     *
     * @var string
     */
    protected $property;

    /**
     * Constructor.
     *
     * @param Entity $entity   The Entity.
     * @param string $property The property.
     */
    public function __construct(Entity $entity, string $property)
    {
        $this->entity = $entity;
        $this->property = $property;
    }

    /**
     * Get the Entity.
     *
     * @return Entity The Entity.
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Get the property.
     *
     * @return string The property.
     */
    public function getProperty()
    {
        return $this->property;
    }
}
