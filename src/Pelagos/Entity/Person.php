<?php
/**
 * This file contains the implementation of the Person entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage Person
 */

namespace Pelagos\Entity;

use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent people.
 */
class Person implements \JsonSerializable
{
    /**
     * Person identifier.
     *
     * @var int $id
     */
    protected $id;

    /**
     * The creation timestamp (in UTC) for this Person.
     *
     * @var \DateTime $creationTimeStamp;
     *
     * @Assert\NotBlank(
     *     message="Creator is required"
     * )
     */
    protected $creationTimeStamp;

    /**
     * The username of the user who created this Person.
     *
     * @var string $creator;
     *
     * @Assert\NotBlank(
     *     message="Creator is required"
     * )
     */
    protected $creator;

    /**
     * Person's first name.
     *
     * @var string $firstName
     *
     * @Assert\NotBlank(
     *     message="First name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="First name cannot contain angle brackets (< or >)"
     * )
     */
    protected $firstName;

    /**
     * Person's last name.
     *
     * @var string $lastName
     *
     * @Assert\NotBlank(
     *     message="Last name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Last name cannot contain angle brackets (< or >)"
     * )
     */
    protected $lastName;

    /**
     * Person's email address.
     *
     * @var string $emailAddress
     *
     * @Assert\NotBlank(
     *     message="Email address is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid"
     * )
     */
    protected $emailAddress;

    /**
     * Person constructor.
     *
     * @param string $firstName    Person's first name.
     * @param string $lastName     Person's last name.
     * @param string $emailAddress Person's email address.
     * @param string $creator      The username of the user who created this Person.
     */
    public function __construct($firstName = null, $lastName = null, $emailAddress = null, $creator = null)
    {
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setEmailAddress($emailAddress);
        $this->setCreator($creator);
        $this->setCreationTimestamp();
    }

    /**
     * Getter for id property.
     *
     * @return int Persistent identifier for the Person.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter for creationTimeStamp property.
     *
     * @param \DateTime $timeStamp Creation timestamp to set.
     *
     * @return void
     *
     * @throws \Exception When $timeStam does not have a timezone of UTC.
     */
    public function setCreationTimestamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ($timeStamp->getTimezone()->getName() != 'UTC') {
                throw new \Exception('creationTimeStamp must be in UTC');
            }
            $this->creationTimeStamp = $timeStamp;
        } else {
            $this->creationTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * Getter for creationTimeStamp property.
     *
     * The default is to return the timestamp localized to the current timezone.
     *
     * @param boolean $localized Whether to convert timestamp to the local timezone.
     *
     * @return \DateTime Creation timestamp for this Person.
     */
    public function getCreationTimestamp($localized = true)
    {
        if ($localized) {
            $timeStamp = clone $this->creationTimeStamp;
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            return $timeStamp;
        }
        return $this->creationTimeStamp;
    }

    /**
     * Setter for creator property.
     *
     * @param string $creator The username of the user who created this Person.
     *
     * @return void
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Getter for creator property.
     *
     * @return string The username of the user who created this Person.
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Setter for firstName property.
     *
     * @param string $firstName First name of the Person.
     *
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Getter for firstName property.
     *
     * @return string First name of the Person.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Setter for lastName property.
     *
     * @param string $lastName Last name of the Person.
     *
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Getter for lastName property.
     *
     * @return string Last name of the Person.
     */
    public function getLastname()
    {
        return $this->lastName;
    }

    /**
     * Setter for emailAddress property.
     *
     * @param string $emailAddress Email address of the Person.
     *
     * @return void
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Getter for emailAddress property.
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
            'creationTimeStamp' => $this->creationTimeStamp->format(\DateTime::ISO8601),
            'creator' => $this->creator,
        );
    }

    /**
     * Method to update multiple properties.
     *
     * @param array $updates An associative array indexed with property names
     *                       and containing each property's new value.
     *
     * @return Person Return the updated object.
     */
    public function update(array $updates)
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
                case 'creator':
                    $this->setCreator($value);
                    break;
            }
        }
        return $this;
    }
}
