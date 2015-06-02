<?php

namespace Pelagos\Entity;

use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;

/**
 * Class to represent people.
 */
class Person
{
    /**
     * Person identifier
     *
     * @var Int
     */
    protected $id;

    /**
     * Person's first name
     *
     * @var String
     */
    protected $firstName;

    /**
     * Person's last name
     *
     * @var String
     */
    protected $lastName;

    /**
     * Person's email address
     *
     * @var String
     */
    protected $emailAddress;

    /**
     * Person constructor
     *
     * @param String $firstName Person's first name
     * @param String $lastName Person's last name
     * @param String $emailAddress Person's email address
     */
    public function __construct($firstName, $lastName, $emailAddress)
    {
        if (empty($firstName)) {
            $this->throwEmptyRequiredArgumentException('firstName', $firstName);
        }
        if (empty($lastName)) {
            $this->throwEmptyRequiredArgumentException('lastName', $lastName);
        }
        if (empty($emailAddress)) {
            $this->throwEmptyRequiredArgumentException('emailAddress', $emailAddress);
        }
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $this->throwInvalidFormatArgumentException('emailAddress', $emailAddress, 'local@domain.tld');
        }
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
    }

    /**
     * id getter
     *
     * @return Int persistent identifier for the Person
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * firstName getter
     *
     * @return String first name of the Person
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * lastName getter
     *
     * @return String last name of the Person
     */
    public function getLastname()
    {
        return $this->lastName;
    }

    /**
     * emailAddress getter
     *
     * @return String email address of the Person
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
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
