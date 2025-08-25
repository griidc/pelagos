<?php

namespace App\Entity;

/**
 * An abstract api for Role objects.
 */
interface RoleInterface
{
    /**
     * Require that implementing classes provide a getName() function that returns the name of the Role.
     *
     * @return string Return the name of this Role object.
     */
    public function getName();

    /**
     * Require that implementing classes provide a getName() function that returns the display weight of the Role.
     *
     * @return int Return the display weight of this object.
     */
    public function getWeight();
}
