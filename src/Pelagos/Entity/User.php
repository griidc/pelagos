<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Implementation of the User class which extends Person.
 *
 * This class defines a User, which is a Person with the
 * addition of credentials.
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
        ),
        'password' => array(
            'type' => 'string',
            'setter' => 'setPassword',
            'getter' => 'getPassword',
        ),
    );

    /**
     * User's ID.
     *
     * @var string $userId
     *
     * @Assert\NotBlank(
     *     message="User ID is required"
     * )
     */
    protected $userId;

    /**
     * User's password.
     *
     * @var string $password
     *
     * @Assert\NotBlank(
     *     message="User password is required"
     * )
     */
    protected $password;

    /**
     * Assign for userId property.
     *
     * @param string $userId User credential user id of a Person.
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
     * @return string User credential user id of a Person.
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Assign for password property.
     *
     * @param string $password User credential password of a Person.
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Retrieve the password property.
     *
     * @return string User credential password of a Person.
     */
    public function getPassword()
    {
        return $this->password;
    }
}
