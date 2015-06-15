<?php

namespace Pelagos\Entity;

use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent people.
 */
class Person implements \JsonSerializable
{
    /** @var int $id Person identifier. */
    protected $id;

    /**
     * Person's first name.
     *
     * @Assert\NotBlank(
     *     message="First name is required"
     * )
     * @Assert\Regex(
     *     pattern="/[<>]/",
     *     match=false,
     *     message="First name cannot contain angle brackets (< or >)"
     * )
     * @var string $firstName
     */
    protected $firstName;

    /**
     * Person's last name.
     *
     * @Assert\NotBlank(
     *     message="Last name is required"
     * )
     * @Assert\Regex(
     *     pattern="/[<>]/",
     *     match=false,
     *     message="Last name cannot contain angle brackets (< or >)"
     * )
     * @var string $lastName
     */
    protected $lastName;

    /**
     * Person's email address.
     *
     * @Assert\NotBlank(
     *     message="Email address is required"
     * )
     * @Assert\Regex(
     *     pattern="/[<>]/",
     *     match=false,
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid"
     * )
     * @var string $emailAddress
     */
    protected $emailAddress;

    /**
     * Person constructor.
     *
     * @param string $firstName Person's first name.
     * @param string $lastName Person's last name.
     * @param string $emailAddress Person's email address.
     */
    public function __construct($firstName, $lastName, $emailAddress)
    {
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setEmailAddress($emailAddress);
    }

    /**
     * id getter
     *
     * @return int Persistent identifier for the Person.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * firstName setter
     *
     * @param string First name of the Person.
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * firstName getter
     *
     * @return string First name of the Person.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * lastName setter
     *
     * @param string $lastName Last name of the Person.
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * lastName getter
     *
     * @return string Last name of the Person.
     */
    public function getLastname()
    {
        return $this->lastName;
    }

    /**
     * emailAddress setter
     *
     * @param string Email address of the Person.
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * emailAddress getter
     *
     * @return string Email address of the Person.
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Implement JsonSerializable.
     *
     * @return array An array suitable for JSON serialization of the object.
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'emailAddress' => $this->emailAddress,
        );
    }

    /**
     * Method to update multiple properties.
     *
     * @param array $updates An associative array indexed with property names
     *                       and containing each property's new value.
     */
    public function update($updates)
    {
        foreach ($updates as $field => $value) {
            switch($field) {
                case 'firstName':
                    $this->setFirstName($value);
                    break;
                case 'lastName':
                    $this->setLastName($value);
                    break;
                case 'emailAddress':
                    $this->setEmailAddress($value);
                    break;
            }
        }
    }
}
