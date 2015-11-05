<?php

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Implementation of the Account class.
 *
 * This class defines an Account, which is a set of credentials for a Person.
 *
 * @package Pelagos\Entity
 */
class Account extends Entity
{
    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array
     *
     * @see Entity
     */
    protected static $properties = array(
        'person' => array(
            'type' => 'object',
            'class' => 'Pelagos\Entity\Person',
            'entity' => 'Person',
            'setter' => 'setPerson',
            'getter' => 'getPerson',
        ),
        'userId' => array(
            'type' => 'string',
            'setter' => 'setUserId',
            'getter' => 'getUserId',
        ),
        'passwordHash' => array(
            'type' => 'string',
            'setter' => 'setPasswordHash',
            'getter' => 'getPasswordHash',
            'serialize' => false,
        ),
        'passwordHashAlgorithm' => array(
            'type' => 'string',
            'setter' => 'setPasswordHashAlgorithm',
            'getter' => 'getPasswordHashAlgorithm',
            'serialize' => false,
        ),
        'passwordHashSalt' => array(
            'type' => 'string',
            'setter' => 'setPasswordHashSalt',
            'getter' => 'getPasswordHashSalt',
            'serialize' => false,
        ),
    );

    /**
     * Person this account is attached to.
     *
     * @var Person
     *
     * @Assert\NotBlank(
     *     message="An account must be attached to a Person"
     * )
     */
    protected $person;

    /**
     * User's ID.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="User ID is required"
     * )
     */
    protected $userId;

    /**
     * A binary string containing the hashed password.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Password hash is required"
     * )
     */
    protected $passwordHash;

    /**
     * The algorithm used to hash the password.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Password hash algorithm is required"
     * )
     */
    protected $passwordHashAlgorithm;

    /**
     * A binary string containing the salt used when hashing the password.
     *
     * @var string
     *
     * @Assert\NotBlank(
     *     message="Password hash salt is required"
     * )
     */
    protected $passwordHashSalt;

    /**
     * Attach a Person to this account.
     *
     * @param Person $person The person to attach to this account.
     *
     * @return void
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
    }

    /**
     * Get the Person this account is attached to.
     *
     * @return Person The Person this account is attached to.
     */
    public function getPerson()
    {
        return $this->person;
    }

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
     * @return string Account user id.
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the password hash.
     *
     * @param string $passwordHash A binary string containing the password hash.
     *
     * @return void
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
    }

    /**
     * Get the password hash.
     *
     * @return string A binary string containing the password hash.
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * Set the password hash algorithm.
     *
     * @param string $passwordHashAlgorithm The algorithm used to hash the password.
     *
     * @return void
     */
    public function setPasswordHashAlgorithm($passwordHashAlgorithm)
    {
        $this->passwordHashAlgorithm = $passwordHashAlgorithm;
    }

    /**
     * Get the password hash algorithm.
     *
     * @return string The algorithm used to hash the password.
     */
    public function getPasswordHashAlgorithm()
    {
        return $this->passwordHashAlgorithm;
    }

    /**
     * Set the password hash salt.
     *
     * @param string $passwordHashSalt A binary string containing the salt used when hashing the password.
     *
     * @return void
     */
    public function setPasswordHashSalt($passwordHashSalt)
    {
        $this->passwordHashSalt = $passwordHashSalt;
    }

    /**
     * Get the password hash salt.
     *
     * @return string A binary string containing the salt used when hashing the password.
     */
    public function getPasswordHashSalt()
    {
        return $this->passwordHashSalt;
    }

    /**
     * Set the password attributes for a provided plain text password.
     *
     * @param string $password Plain text password.
     *
     * @return void
     */
    public function setPassword($password)
    {
        mt_srand((double) (microtime() * 1000000));
        $this->setPasswordHashAlgorithm('SSHA');
        $this->setPasswordHashSalt(pack('CCCC', mt_rand(), mt_rand(), mt_rand(), mt_rand()));
        $this->setPasswordHash(sha1($password . $this->getPasswordHashSalt(), true));
    }

    /**
     * Compare a plain text password against the hashed password.
     *
     * @param string $password Plain text password.
     *
     * @return boolean Whether or not the provided password matches the hash.
     */
    public function comparePassword($password)
    {
        $hash = sha1($password . $this->getPasswordHashSalt(), true);
        if ($hash === $this->getPasswordHash()) {
            return true;
        }
        return false;
    }
}
