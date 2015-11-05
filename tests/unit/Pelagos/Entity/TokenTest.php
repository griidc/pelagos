<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for Pelagos\Entity\Token.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\Token
 *
 * @package Pelagos\Entity
 */
class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of Token for testing.
     * @var Token
     */
    protected $token;

    /**
     * Static class variable containing token text to use for testing.
     * @var string
     */
    // sha256sum of 'test'
    protected static $tokenText = 'f2ca1bb6c7e907d06dafe4687e579fce76b37e4e93b7605022da52e6ccc26fd2';


    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of User and sets its properties.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->token = new Token;
        $this->token->setTokenText(self::$tokenText);
    }

    /**
     * Test the getUserId method.
     *
     * This method should return the userId that was assigned in setUp.
     *
     * @return void
     */
    public function testGetTokenText()
    {
        $this->assertEquals(
            $this->token->getTokenText(),
            self::$tokenText
        );
    }
}
