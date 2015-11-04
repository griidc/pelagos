<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for Pelagos\Entity\User.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\User
 *
 * @package Pelagos\Entity
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of User for testing.
     * @var User
     */
    protected $user;

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
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of User and sets its properties.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->user = new User;
        $this->user->setUserId(self::$userId);
        $this->user->setPassword(self::$password);
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
            $this->user->getUserId(),
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
            $this->user->getPassword(),
            self::$password
        );
    }
}
