<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

require_once 'PersonTest.php';

/**
 * Unit tests for Pelagos\Entity\Person.
 *
   @group   Pelagos
   @group   Pelagos\Entity
   @group   Pelagos\Entity\Person
   @group   Pelagos\Entity\User
   @package Pelagos\Entity
 */
class UserTest extends PersonTest
{
    /**
     * Static class variable containing a user id to use for testing.
     * @var string
     */
    protected static $userId = 'MyUserId';

    /**
     * Static class variable containing a password to use for testing.
     * @var string
     */
    protected static $password = 'MyP@$$w0rd';

    /**
     * Instantiate the Validation object and the test object.
     *
     * @return void
     */
    protected function create()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $this->person = new User;
    }

    /**
     * Setup for PHPUnit tests.
     *
     * This includes the autoloader and instantiates an instance of User.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->person->setUserId(self::$userId);
        $this->person->setPassword(self::$password);
    }

    /**
     * Test the getUserId method.
     *
     * This method should return the userId that was assigned in setUp.
     *
     * @return void
     */
    public function testGetUserId()
    {
        $this->assertEquals(
            $this->person->getUserId(),
            self::$userId
        );
    }

    /**
     * Test the getPassword method.
     *
     * This method should return the password that was assigned in setUp.
     *
     * @return void
     */
    public function testGetPassword()
    {
        $this->assertEquals(
            $this->person->getPassword(),
            self::$password
        );
    }

    /**
     * Get an array of all the state values.
     *
     * Add this objects state to that of the parent.
     * Used for testing JSON serialization.
     * This function overrides the parent implementation.
     *
     * @return array Return an array of state of this and the parent object.
     */
    public function getStateArray()
    {
        $personData = parent::getStateArray();
        $personData['userId'] = self::$userId;
        $personData['password'] = self::$password;
        return $personData;
    }
}
