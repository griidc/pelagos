<?php

namespace Pelagos\Exception;

/**
 * Custom exception for when a record is not found in persistence.
 */
class RecordNotFoundPersistenceException extends PersistenceException
{
    /** @var mixed $id Property to hold the id of the entity that was searched for. **/
    protected $id;

    /**
     * Setter for $id
     *
     * @param mixed $id The id of the entity that was searched for.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Getter for $id
     *
     * @return mixed The id of the entity that was searched for.
     */
    public function getId()
    {
        return $this->id;
    }
}
