<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for Pelagos\Entity\PersonToken.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\PersonToken
 *
 * @package Pelagos\Entity
 */
class PersonTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of PersonToken for testing.
     * @var PersonToken
     */
    protected $personToken;

    /**
     * Property to hold a mock instance of Person for testing.
     * @var Person
     */
    protected $mockPerson;

    /**
     * Property to hold a hexadecimal representation of a null byte token.
     * @var PersonToken
     */
    protected $nullTokenText;

    /**
     * Property to hold a test use.
     * @var PersonTokenUse
     */
    protected $personTokenUse;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of PersonToken.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->mockPerson = \Mockery::mock(
            '\Pelagos\Entity\Person',
            array(
                'setToken' => null,
                'getToken' => null
            )
        );
        // Create a 32 null byte token.
        $this->nullTokenText = bin2hex(str_repeat("\0", 32));
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = true;
        $this->persontokenUse = 'CREATE_ACCOUNT';
        $this->personToken = new PersonToken($this->mockPerson, $this->personTokenUse, new \DateInterval('P1W'));
        // Sets entity's creationTimeStamp to now.
        $this->personToken->setCreationTimeStamp();
    }

    /**
     * Test the getPerson method.
     *
     * This method should return the same Person set in setUp.
     *
     * @return void
     */
    public function testGetPerson()
    {
        $this->assertSame(
            $this->mockPerson,
            $this->personToken->getPerson()
        );
    }

    /**
     * Test the getUse method.
     *
     * This method should return the same use value set in setUp.
     *
     * @return void
     */
    public function testGetUse()
    {
        $this->assertEquals(
            $this->personTokenUse,
            $this->personToken->getUse()
        );
    }

    /**
     * Test the getTokenText method.
     *
     * This method should return the text string for the token.
     *
     * @return void
     */
    public function testGetTokenText()
    {
        $this->assertEquals(
            $this->nullTokenText,
            $this->personToken->getTokenText()
        );
    }

    /**
     * Test that an exception is thrown when we are unable to generate a cryptographically strong token.
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testUnableToGenerateCryptographicallyStrongToken()
    {
        // Tell our mock openssl_random_pseudo_bytes function to report that
        // it was NOT able to generate a cryptographically strong byte string.
        $GLOBALS['cryptoStrong'] = false;
        new PersonToken($this->mockPerson, $this->personTokenUse, new \DateInterval('P1W'));
    }

    /**
     * Test the isValid method with a non-expired token.
     *
     * @return void
     */
    public function testAffirmIsValid()
    {
        $this->personToken->setValidFor(\DateInterval::createFromDateString('1 week'));
        $this->assertTrue($this->personToken->isValid($this->personToken));
    }

    /**
     * Test the isValid method with an expired token.
     *
     * @return void
     */
    public function testAffirmIsNotValid()
    {
        $this->personToken->setValidFor(\DateInterval::createFromDateString('- 1 week'));
        $this->assertFalse($this->personToken->isValid($this->personToken));
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
