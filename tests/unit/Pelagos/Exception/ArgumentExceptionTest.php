<?php

namespace Pelagos\Exception;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Exception\ArgumentException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\ArgumentException
 */
class ArgumentExceptionTest extends TestCase
{
    /**
     * Static class variable containing an argument name to use for testing.
     *
     * @var string $testArgumentName
     */
    protected static $testArgumentName = 'fooBar';

    /**
     * Static class variable containing an argument value to use for testing.
     *
     * @var string $testArgumentValue
     */
    protected static $testArgumentValue = 123;

    /**
     * Test that we can throw an ArgumentException.
     *
     * @expectedException \Pelagos\Exception\ArgumentException
     *
     * @throws ArgumentException Always.
     *
     * @return void
     */
    public function testThrowArgumentException()
    {
        throw new ArgumentException();
    }

    /**
     * Test that we can catch an ArgumentException.
     *
     * @throws ArgumentException Always, but it's caught.
     *
     * @return void
     */
    public function testCatchArgumentException()
    {
        try {
            throw new ArgumentException();
        } catch (ArgumentException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch an ArgumentException as an \Exception.
     *
     * @throws ArgumentException Always, but it's caught as an \Exception.
     *
     * @return void
     */
    public function testCatchException()
    {
        try {
            throw new ArgumentException();
        } catch (\Exception $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can set the argument name and get it back.
     *
     * @return void
     */
    public function testSetArgumentName()
    {
        $exception = new ArgumentException();
        $exception->setArgumentName(self::$testArgumentName);
        $this->assertEquals(
            $exception->getArgumentName(),
            self::$testArgumentName
        );
    }

    /**
     * Test that we can set the argument name and get it back upon catching the exception.
     *
     * @throws ArgumentException Always, but it's caught.
     *
     * @return void
     */
    public function testGetArgumentNameOnCatch()
    {
        $exception = new ArgumentException();
        $exception->setArgumentName(self::$testArgumentName);
        try {
            throw $exception;
        } catch (ArgumentException $e) {
            $this->assertEquals(
                $e->getArgumentName(),
                self::$testArgumentName
            );
        }
    }

    /**
     * Test that we can set the argument value and get it back.
     *
     * @return void
     */
    public function testSetArgumentValue()
    {
        $exception = new ArgumentException();
        $exception->setArgumentValue(self::$testArgumentValue);
        $this->assertEquals(
            $exception->getArgumentValue(),
            self::$testArgumentValue
        );
    }

    /**
     * Test that we can set the argument value and get it back upon catching the exception.
     *
     * @throws ArgumentException Always, but it's caught.
     *
     * @return void
     */
    public function testGetArgumentValueOnCatch()
    {
        $exception = new ArgumentException();
        $exception->setArgumentValue(self::$testArgumentValue);
        try {
            throw $exception;
        } catch (ArgumentException $e) {
            $this->assertEquals(
                $e->getArgumentValue(),
                self::$testArgumentValue
            );
        }
    }
}
