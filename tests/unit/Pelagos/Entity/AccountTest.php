<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for Pelagos\Entity\Account.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\Account
 *
 * @package Pelagos\Entity
 */
class AccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of Account for testing.
     * @var Account
     */
    protected $account;

    /**
     * Property to hold a mock instance of Person for testing.
     * @var Person
     */
    protected $mockPerson;

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
     * This instantiates an instance of Account and sets its properties.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mockPerson = \Mockery::mock('\Pelagos\Entity\Person');
        $this->account = new Account;
        $this->account->setPerson($this->mockPerson);
        $this->account->setUserId(self::$userId);
        $this->account->setPassword(self::$password);
    }

    /**
     * Test the getPerson method.
     *
     * This method should return the Person that was assigned in setUp.
     *
     * @return void
     */
    public function testGetPerson()
    {
        $this->assertSame(
            $this->mockPerson,
            $this->account->getPerson()
        );
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
            self::$userId,
            $this->account->getUserId()
        );
    }

    /**
     * Test the comparePassword method.
     *
     * This method should return true id the provided password matches the
     * password that was assigned in setUp and false when it does not.
     *
     * @return void
     */
    public function testComparePassword()
    {
        $this->assertTrue($this->account->comparePassword(self::$password));
        $this->assertFalse($this->account->comparePassword('something else'));
    }
}
