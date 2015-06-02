<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\ArgumentException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\ArgumentException
 */
class ArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var string $testArgumentName Static class variable containing an argument name to use for testing */
    protected static $testArgumentName = 'fooBar';

    /** @var string $testArgumentValue Static class variable containing an argument value to use for testing */
    protected static $testArgumentValue = 123;

    /**
     * Test that we can throw an ArgumentException.
     *
     * @expectedException \Pelagos\Exception\ArgumentException
     */
    public function testThrowArgumentException()
    {
        throw new ArgumentException();
    }

    /**
     * Test that we can catch an ArgumentException.
     */
    public function testCatchArgumentException()
    {
        try {
            throw new ArgumentException();
        } catch (ArgumentException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch an ArgumentException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new ArgumentException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can set the argument name and get it back.
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
