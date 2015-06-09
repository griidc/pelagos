<?php

namespace Pelagos\Entity;

use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;

/**
 * Class to represent people.
 */
class Person implements \JsonSerializable
{
    /** @var int $id Person identifier. */
    protected $id;

    /** @var string $firstName Person's first name. */
    protected $firstName;

    /** @var string $lastName Person's last name. */
    protected $lastName;

    /** @var string $emailAddress Person's email address. */
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
        if (empty($firstName)) {
            $this->throwEmptyRequiredArgumentException('firstName', $firstName);
        }
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
        if (empty($lastName)) {
            $this->throwEmptyRequiredArgumentException('lastName', $lastName);
        }
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
        if (empty($emailAddress)) {
            $this->throwEmptyRequiredArgumentException('emailAddress', $emailAddress);
        }
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $this->throwInvalidFormatArgumentException('emailAddress', $emailAddress, 'local@domain.tld');
        }
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

    /**
     * Method to throw EmptyRequiredArgumentException with properties set.
     *
     * @param $argumentName string Name of the empty required argument.
     * @param $argumentValue mixed Value of the empty required argument.
     */
    protected function throwEmptyRequiredArgumentException($argumentName, $argumentValue)
    {
        $exception = new EmptyRequiredArgumentException("$argumentName is required");
        $exception->setArgumentName($argumentName);
        $exception->setArgumentValue($argumentValue);
        throw $exception;
    }

    /**
     * Method to throw InvalidFormatArgumentException with properties set.
     *
     * @param $argumentName string Name of the invalidly formatted argument.
     * @param $argumentValue mixed Value of the invalidly formatted argument.
     * @param $expectedFormat string Text description of the expected format for the argument.
     */
    protected function throwInvalidFormatArgumentException($argumentName, $argumentValue, $expectedFormat = null)
    {
        $exception = new InvalidFormatArgumentException("$argumentName is improperly formatted");
        $exception->setArgumentName($argumentName);
        $exception->setArgumentValue($argumentValue);
        $exception->setExpectedFormat($expectedFormat);
        throw $exception;
    }
}
