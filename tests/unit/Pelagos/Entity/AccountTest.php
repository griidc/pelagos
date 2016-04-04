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
     * Property to hold a mock instance of Password for testing.
     * @var Password
     */
    protected $mockPassword;

    /**
     * Static class variable containing a user id to use for testing.
     * @var string
     */
    protected static $userId = 'MyUserId';

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Account and sets its properties.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mockPerson = \Mockery::mock(
            '\Pelagos\Entity\Person',
            array(
                'setAccount' => null,
                'getAccount' => null
            )
        );
        $this->mockPassword = \Mockery::mock(
            '\Pelagos\Entity\Password',
            array(
                'setAccount' => null,
                'getPasswordHash' => null,
                'getClearTextPassword' => null
            )
        );
        $this->account = new Account($this->mockPerson, self::$userId, $this->mockPassword);
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
     * Clean up after tests.
     *
     * @return void
     */
    public function tearDown()
    {
    }
}
