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
     * @var string $testInvalidEmailAddress Static class variable containing an invalid email address to use for testing
     */
    protected static $testInvalidEmailAddress = 'foo@bar@com';

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

    /**
     * Test constructing a Person with a null first name.
     * An InvalidArgumentException should be thrown.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNullFirstName()
    {
        $this->person = new Person(
            null,
            self::$testLastName,
            self::$testEmailAddress
        );
    }

    /**
     * Test constructing a Person with an empty first name.
     * An InvalidArgumentException should be thrown.
     *
     * @expectedException InvalidArgumentException
     */
    public function testEmptyFirstName()
    {
        $this->person = new Person(
            '',
            self::$testLastName,
            self::$testEmailAddress
        );
    }

    /**
     * Test constructing a Person with a null last name.
     * An InvalidArgumentException should be thrown.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNullLastName()
    {
        $this->person = new Person(
            self::$testFirstName,
            null,
            self::$testEmailAddress
        );
    }

    /**
     * Test constructing a Person with an empty last name.
     * An InvalidArgumentException should be thrown.
     *
     * @expectedException InvalidArgumentException
     */
    public function testEmptyLastName()
    {
        $this->person = new Person(
            self::$testFirstName,
            '',
            self::$testEmailAddress
        );
    }

    /**
     * Test constructing a Person with a null email address.
     * An InvalidArgumentException should be thrown.
     *
     * @expectedException InvalidArgumentException
     */
    public function testNullEmailAddress()
    {
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            null
        );
    }

    /**
     * Test constructing a Person with an empty email address.
     * An InvalidArgumentException should be thrown.
     *
     * @expectedException InvalidArgumentException
     */
    public function testEmptyEmailAddress()
    {
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            ''
        );
    }

    /**
     * Test constructing a Person with an invalid address.
     * An InvalidArgumentException should be thrown.
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidEmailAddress()
    {
        $this->person = new Person(
            self::$testFirstName,
            self::$testLastName,
            self::$testInvalidEmailAddress
        );
    }
}
