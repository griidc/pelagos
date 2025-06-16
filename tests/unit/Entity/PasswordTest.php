<?php

namespace App\Tests\Entity;

use App\Entity\Account;
use App\Entity\Password;
use App\Exception\PasswordException;
use PHPUnit\Framework\TestCase;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for App\Entity\Password.
 */
class PasswordTest extends TestCase
{
    /**
     * Property to hold an instance of Password for testing.
     * @var Password
     */
    protected $password;

    /**
     * Property to hold a mock instance of Account for testing.
     * @var Account
     */
    protected $mockAccount;

    /**
     * Static class variable containing a password to use for testing.
     * @var string
     */
    protected static $passwordString = 'MyP@$$w0rd';

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Account and sets its properties.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mockAccount = \Mockery::mock(
            '\App\Entity\Account'
        );
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = true;
        $this->password = new Password(self::$passwordString);
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
        $this->assertTrue($this->password->comparePassword(self::$passwordString));
        $this->assertFalse($this->password->comparePassword('bad password'));
    }

    /**
     * Test constructor's setting of cleartext password.
     *
     * This method should return true if the cleartext representation
     * matches the password set via the constructor.
     *
     * @return void
     */
    public function testClearTextPassword()
    {
        $this->assertSame($this->password->getClearTextPassword(), self::$passwordString);
    }

    /**
     * Test that a PasswordException is thrown when we attempt to set a password that is too short.
     *
     * @return void
     */
    public function testSetPasswordTooShort()
    {
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = true;
        $this->expectException(PasswordException::class);
        $this->password->setPassword('Sh*rtpw');
    }

    /**
     * Test that a PasswordException is thrown when we attempt to set a password that is not complex enough.
     *
     * @return void
     */
    public function testSetPasswordNotComplexEnough()
    {
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = true;
        $this->expectException(PasswordException::class);
        $this->password->setPassword('SimplePassword');
    }

    /**
     * Test that an Account can be set in the Password entity.
     *
     * @return void
     */
    public function testSetAccount()
    {
        $this->password->setAccount($this->mockAccount);
        $this->assertSame($this->password->getAccount(), $this->mockAccount);
    }

    /**
     * Test that the salt can be retrieved from the Password entity.
     *
     * @return void
     */
    public function testGetSalt()
    {
        $this->assertNotNull($this->password->getSalt());
    }

    /**
     * Test that the hash can be retrieved from the Password entity.
     *
     * @return void
     */
    public function testGetHash()
    {
        $this->assertNotNull($this->password->getPasswordHash());
    }

    /**
     * Test that the SSHA hash can be returned.
     *
     * @return void
     */
    public function testGetSSHAHash()
    {
        $this->assertNotNull($this->password->getSSHAPassword());
    }

    /**
     * Test that the hash alrorithm can be retrieved from the Password entity.
     *
     * @return void
     */
    public function testGetHashAlgorithm()
    {
        $this->assertEquals($this->password->getHashAlgorithm(), 'SSHA');
    }

    /**
     * Clean up after tests.
     *
     * @return void
     */
    public function tearDown(): void
    {
        // Unset the 'cryptoStrong' global so as not to interfere with any
        // other tests of code that uses openssl_random_pseudo_bytes
        unset($GLOBALS['cryptoStrong']);
    }
}
