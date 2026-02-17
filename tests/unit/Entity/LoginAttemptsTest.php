<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Account;
use App\Entity\LoginAttempts;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\LoginAttempts.
 */
class LoginAttemptsTest extends TestCase
{
    /**
     * The LoginAttempts instance to be tested.
     */
    private LoginAttempts $loginAttempts;

    /**
     * A mock Account object.
     * @var Account|\Mockery\MockInterface
     */
    private $mockAccount;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->mockAccount = \Mockery::mock(Account::class);
        $this->loginAttempts = new LoginAttempts($this->mockAccount);
    }

    /**
     * Test that the constructor sets the account.
     */
    public function testConstructorSetsAccount(): void
    {
        $reflectionClass = new \ReflectionClass(LoginAttempts::class);
        $accountProperty = $reflectionClass->getProperty('account');
        $accountProperty->setAccessible(true);

        $this->assertSame($this->mockAccount, $accountProperty->getValue($this->loginAttempts));
    }
}
