<?php
/**
 * This file contains an abstract implementation of a Pelagos entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage Entity
 */

namespace Pelagos\Entity;

/**
 * Abstract class that contains basic properties and methods common to all Pelagos entities.
 */
abstract class Entity
{
    /**
     * Entity identifier.
     *
     * @var int $id
     */
    protected $id;

    /**
     * Getter for id property.
     *
     * @return int Persistent identifier for the Entity.
     */
    public function getId()
    {
        return $this->id;
    }
}
