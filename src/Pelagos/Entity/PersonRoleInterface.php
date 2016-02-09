<?php
/**
 * Interface PersonRoleInterface. An abstract api for classes that contain a RoleInterface attribute.
 *
 * @package Pelagos\Entity
 */

namespace Pelagos\Entity;

interface PersonRoleInterface
{

    /**
     * Require that implementing classes provide a getRole() function that returns the RoleInterface object.
     *
     * @return string Return the name RoleInterface attribute.
     */
    public function getRole();

    /**
     * Require that implementing classes provide a getLabel() function..
     *
     * @return string Return the label attribute of this object.
     */
    public function getLabel();

    /**
     * Require that implementing classes provide a getPerson() function..
     *
     * @return string Return the Person attribute of this object.
     */
    public function getPerson();
}
