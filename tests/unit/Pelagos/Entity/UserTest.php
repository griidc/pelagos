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
     * Static class variable containing a first name to use for testing.
     * @var string
     */
    protected static $userId = 'MyUserId';

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
     * Get an array of all the state values.
     *
     * Add this objects state to that of the parent.
     * Used for testing JSON serialization..
     *
     * @return array Return an array of state of this and the parent object.
     */
    public function getStateArray()
    {
        $personData = parent::getStateArray();
        $personData['userId'] = self::$userId;
        return $personData;
    }
}
