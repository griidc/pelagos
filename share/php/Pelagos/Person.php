<?php

namespace Pelagos;

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
     * @param Int $id Person's internal id (if defined)
     */
    public function __construct($firstName, $lastName, $emailAddress, $id = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->emailAddress = $emailAddress;
        $this->id = $id;
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
}
