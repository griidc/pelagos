<?php

namespace Pelagos\Entity;

/**
 * An abstract api for classes that contain a RoleInterface attribute.
 */
interface PersonAssociationInterface
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
     * @return RoleInterface Return the label attribute of this object.
     */
    public function getLabel();

    /**
     * Require that implementing classes provide a getPerson() function..
     *
     * @return Person Return the Person attribute of this object.
     */
    public function getPerson();
}
