<?php

namespace Pelagos\Entity;

/**
 * Unit tests for Pelagos\Entity\Person.
 */
class PersonTest extends \PHPUnit_Framework_TestCase
{
    /** @var Person $person Property to hold an instance of Person for testing */
    protected $person;

    /** @var string $testFirstName Static class variable containing a first name to use for testing */
    protected static $testFirstName = 'MyFirstName';

    /** @var string $testLastName Static class variable containing a last name to use for testing */
    protected static $testLastName = 'MyLastName';

    /** @var string $testEmailAddress Static class variable containing an email address to use for testing */
    protected static $testEmailAddress = 'foo@bar.com';

    /**
     * Setup for PHPUnit tests.
     * This includes the autoloader and instantiates an instance of Person.
     */
    protected function setUp()
    {
        require_once __DIR__ . '/../../../../vendor/autoload.php';
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            self::$testEmailAddress
        );
    }

    /**
     * Test the getId method.
     * This method should always return null because it can not be set (even by the constructor).
     * The id property can only be set when a Person is instantiated from persistence by Doctrine.
     */
    public function testGetID()
    {
        $this->assertEquals(
            $this->person->getId(),
            null
        );
    }

    /**
     * Test the getFirstName method.
     * This method should return the first name that was passed to the constructor.
     */
    public function testGetFirstName()
    {
        $this->assertEquals(
            $this->person->getFirstName(),
            self::$testFirstName
        );
    }

    /**
     * Test the getLastName method.
     * This method should return the last name that was passed to the constructor.
     */
    public function testGetLastName()
    {
        $this->assertEquals(
            $this->person->getLastName(),
            self::$testLastName
        );
    }

    /**
     * Test the getEmailAddress method.
     * This method should return the email address that was passed to the constructor.
     */
    public function testGetEmailAddress()
    {
        $this->assertEquals(
            $this->person->getEmailAddress(),
            self::$testEmailAddress
        );
    }
}
