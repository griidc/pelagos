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
     * The creation time stamp (in UTC) for this Person.
     *
     * @var \DateTime $creationTimeStamp;
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
     * The last modification time stamp (in UTC) for this Person.
     *
     * @var \DateTime $modificationTimeStamp;
     */
    protected $modificationTimeStamp;

    /**
     * The username of the user who last modified this Person.
     *
     * @var string $creator;
     *
     * @Assert\NotBlank(
     *     message="Modifier is required"
     * )
     */
    protected $modifier;

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
     * Setting the creation time stamp also sets the modification time stamp.
     *
     * @param \DateTime $timeStamp Creation time stamp to set.
     *
     * @return void
     *
     * @throws \Exception When $timeStamp does not have a timezone of UTC.
     */
    public function setCreationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ($timeStamp->getTimezone()->getName() != 'UTC') {
                throw new \Exception('creationTimeStamp must be in UTC');
            }
            $this->creationTimeStamp = $timeStamp;
        } else {
            $this->creationTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $this->modificationTimeStamp = $this->creationTimeStamp;
    }

    /**
     * Getter for creationTimeStamp property.
     *
     * The default is to return the time stamp in UTC.
     * Setting $localized to true will retunr the time stamp localized to the current timezone.
     * This getter also makes sure the creationTimeStamp property is set to UTC.
     *
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return \DateTime Creation time stamp for this Person.
     */
    public function getCreationTimeStamp($localized = false)
    {
        if (!isset($this->creationTimeStamp)) {
            return null;
        }
        $this->creationTimeStamp->setTimeZone(new \DateTimeZone('UTC'));
        if ($localized) {
            $timeStamp = clone $this->creationTimeStamp;
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            return $timeStamp;
        }
        return $this->creationTimeStamp;
    }

    /**
     * Get the creationTimeStamp property as an ISO8601 string.
     *
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return string ISO8601 string representing creationTimeStamp.
     */
    public function getCreationTimeStampAsISO($localized = false)
    {
        if (isset($this->creationTimeStamp) and $this->creationTimeStamp instanceof \DateTime) {
            return $this->getCreationTimeStamp($localized)->format(\DateTime::ISO8601);
        }
        return null;
    }

    /**
     * Setter for creator property.
     *
     * Setting the creator also sets the modifier.
     *
     * @param string $creator The username of the user who created this Person.
     *
     * @return void
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
        $this->modifier = $creator;
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
     * Setter for modificationTimeStamp property.
     *
     * @param \DateTime $timeStamp Modification time stamp to set.
     *
     * @return void
     *
     * @throws \Exception When $timeStamp does not have a timezone of UTC.
     */
    public function setModificationTimeStamp(\DateTime $timeStamp = null)
    {
        if (isset($timeStamp)) {
            if ($timeStamp->getTimezone()->getName() != 'UTC') {
                throw new \Exception('modificationTimeStamp must be in UTC');
            }
            $this->modificationTimeStamp = $timeStamp;
        } else {
            $this->modificationTimeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * Getter for modificationTimeStamp property.
     *
     * The default is to return the time stamp localized to the current timezone.
     * This getter also makes sure the modificationTimeStamp property is set to UTC.
     *
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return \DateTime Modification time stamp for this Person.
     */
    public function getModificationTimeStamp($localized = false)
    {
        if (!isset($this->modificationTimeStamp)) {
            return null;
        }
        $this->modificationTimeStamp->setTimeZone(new \DateTimeZone('UTC'));
        if ($localized) {
            $timeStamp = clone $this->modificationTimeStamp;
            $timeStamp->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            return $timeStamp;
        }
        return $this->modificationTimeStamp;
    }

    /**
     * Get the modificationTimeStamp property as an ISO8601 string.
     *
     * @param boolean $localized Whether to convert time stamp to the local timezone.
     *
     * @return string ISO8601 string representing modificationTimeStamp.
     */
    public function getModificationTimeStampAsISO($localized = false)
    {
        if (isset($this->modificationTimeStamp) and $this->modificationTimeStamp instanceof \DateTime) {
            return $this->getModificationTimeStamp($localized)->format(\DateTime::ISO8601);
        }
        return null;
    }

    /**
     * Setter for modifier property.
     *
     * @param string $modifier The username of the user who modified this Person.
     *
     * @return void
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * Getter for modifier property.
     *
     * @return string The username of the user who modified this Person.
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * Update the time stamps to the current time.
     *
     * The creation time stamp is only updated if not already set.
     *
     * @return void
     */
    public function updateTimeStamps()
    {
        if ($this->creationTimeStamp == null) {
            $this->setCreationTimeStamp();
        }
        $this->setModificationTimeStamp();
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
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'emailAddress' => $this->getEmailAddress(),
            'creationTimeStamp' => $this->getCreationTimeStampAsISO(),
            'creator' => $this->getCreator(),
            'modificationTimeStamp' => $this->getModificationTimeStampAsISO(),
            'modifier' => $this->getModifier(),
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

    /**
     * Method that returns a Person's properties as an array.
     *
     * Default is to not localize time stamps.
     *
     * @param array   $properties         An array listing the properties to include.
     * @param boolean $localizeTimestamps A flag to inidcate whether or not to localize time stamps.
     *
     * @return array An array of property values for this Person.
     */
    public function asArray(array $properties, $localizeTimestamps = false)
    {
        $personArray = array();
        foreach ($properties as $property) {
            switch($property) {
                case 'id':
                    $personArray[] = $this->getId();
                    break;
                case 'firstName':
                    $personArray[] = $this->getFirstName();
                    break;
                case 'lastName':
                    $personArray[] = $this->getLastName();
                    break;
                case 'emailAddress':
                    $personArray[] = $this->getEmailAddress();
                    break;
                case 'creationTimeStamp':
                    $personArray[] = $this->getCreationTimeStampAsISO($localizeTimestamps);
                    break;
                case 'creator':
                    $personArray[] = $this->getCreator();
                    break;
                case 'modificationTimeStamp':
                    $personArray[] = $this->getModificationTimeStampAsISO($localizeTimestamps);
                    break;
                case 'modifier':
                    $personArray[] = $this->getModifier();
                    break;
            }
        }
        return $personArray;
    }
}
