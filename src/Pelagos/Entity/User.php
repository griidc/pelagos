<?php

namespace Pelagos\Entity;

use \Pelagos\Exception\NotDeletableException;
use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Implementation of the User class which extends Person.
 *
 * This class defines a User which is a Person with the
 * addition of User ID credential
 *
 * @package Pelagos\Entity
 */
class User extends Person
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * Used by common update code.
     *
     * @var array $properties
     * @see Entity
     */
    protected static $properties = array(
        'userId' => array(
            'type' => 'string',
            'setter' => 'setUserId',
            'getter' => 'getUserId',
        )
    );

    /**
     * User's ID .
     *
     * @var string $userId
     *
     * @Assert\NotBlank(
     *     message="User ID is required"
     * )
     */
    protected $userId;

    /**
     * Assign for userId property.
     *
     * @param string $userId GRIIDC user credential of a Person.
     *
     * @return void
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Retrieve the userId property.
     *
     * @return string GRIIDC user credential of a Person.
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
