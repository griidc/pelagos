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
        $this->mockPerson = \Mockery::mock(
            '\Pelagos\Entity\Person',
            array(
                'setAccount' => null,
                'getAccount' => null,
            )
        );
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = true;
        $this->account = new Account($this->mockPerson, self::$userId, self::$password);
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

    /**
     * Test that an exception is thrown when we are unable to generate a cryptographically strong password hash salt.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testSetPasswordUnableToGenerateCryptographicallyStrongSalt()
    {
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was NOT able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = false;
        $this->account->setPassword(self::$password);
    }

    /**
     * Test that a PasswordException is thrown when we attempt to set a password that is too short.
     *
     * @expectedException \Pelagos\Exception\PasswordException
     *
     * @return void
     */
    public function testSetPasswordTooShort()
    {
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = true;
        $this->account->setPassword('Sh*rtpw');
    }

    /**
     * Test that a PasswordException is thrown when we attempt to set a password that is not complex enough.
     *
     * @expectedException \Pelagos\Exception\PasswordException
     *
     * @return void
     */
    public function testSetPasswordNotComplexEnough()
    {
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = true;
        $this->account->setPassword('SimplePassword');
    }

    /**
     * Clean up after tests.
     *
     * @return void
     */
    public function tearDown()
    {
        // Unset the 'cryptoStrong' global so as not to interfere with any
        // other tests of code that uses openssl_random_pseudo_bytes
        unset($GLOBALS['cryptoStrong']);
    }
}
