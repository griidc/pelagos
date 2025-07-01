<?php

namespace App\Exception;

/**
 * Custom exception for when an Entity is not deletable.
 */
class NotDeletableException extends \Exception
{
    /**
     * List of reasons an entity is not deletable.
     *
     * @var array
     */
    protected $reasons = [];

    /**
     * Set the list of reasons an entity is not deletable.
     *
     * @param array $reasons List of reasons an entity is not deletable.
     *
     * @return void
     */
    public function setReasons(array $reasons)
    {
        $this->reasons = $reasons;
        $this->message = 'Entity is not deletable: ' . implode(', ', $reasons);
    }

    /**
     * Get the list of reasons an entity is not deletable.
     *
     * @return array List of reasons an entity is not deletable.
     */
    public function getReasons()
    {
        return $this->reasons;
    }
}
