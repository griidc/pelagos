<?php

namespace App\Entity;

use App\Exception\NotDeletableException;

trait EntityTrait
{
    /**
     * Checks if the entity is deletable.
     */
    public function isDeletable(): bool
    {
        try {
            $this->checkDeletable();
        } catch (NotDeletableException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the name of this entity lowercased and separated by underscores.
     */
    public function getUnderscoredName(): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', substr(strrchr(get_class($this), '\\'), 1)));
    }
}