<?php

namespace Pelagos\Exception;

/**
 * Custom exception for when a record is not found in persistence.
 */
class RecordNotFoundPersistenceException extends PersistenceException
{
    /**
     * Property to hold the id of the entity that was searched for.
     *
     * @var mixed $id
     */
    protected $id;

    /**
     * Setter for $id.
     *
     * @param mixed $id The id of the entity that was searched for.
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Getter for $id.
     *
     * @return mixed The id of the entity that was searched for.
     */
    public function getId()
    {
        return $this->id;
    }
}
