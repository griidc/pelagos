<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\InvalidFormatArgumentException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\ArgumentException
 */
class InvalidFormatArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Static class variable containing an expected format to use for testing.
     *
     * @var string $testExpectedFormat
     */
    protected static $testExpectedFormat = 'yyyy-mm-dd';

    /**
     * Test that we can throw an InvalidFormatArgumentException.
     *
     * @expectedException \Pelagos\Exception\InvalidFormatArgumentException
     *
     * @throws InvalidFormatArgumentException Always.
     *
     * @return void
     */
    public function testThrowInvalidFormatArgumentException()
    {
        throw new InvalidFormatArgumentException();
    }

    /**
     * Test that we can catch a InvalidFormatArgumentException.
     *
     * @throws InvalidFormatArgumentException Always, but it's caught.
     *
     * @return void
     */
    public function testCatchInvalidFormatArgumentException()
    {
        try {
            throw new InvalidFormatArgumentException();
        } catch (InvalidFormatArgumentException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a InvalidFormatArgumentException as an ArgumentException.
     *
     * @throws InvalidFormatArgumentException Always, but it's caught as an ArgumentException..
     *
     * @return void
     */
    public function testCatchArgumentException()
    {
        try {
            throw new InvalidFormatArgumentException();
        } catch (ArgumentException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a InvalidFormatArgumentException as an \Exception.
     *
     * @throws InvalidFormatArgumentException Always, but it's caught as an \Exception.
     *
     * @return void
     */
    public function testCatchException()
    {
        try {
            throw new InvalidFormatArgumentException();
        } catch (\Exception $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can set the expected format and get it back.
     *
     * @return void
     */
    public function testSetExpectedFormat()
    {
        $exception = new InvalidFormatArgumentException();
        $exception->setExpectedFormat(self::$testExpectedFormat);
        $this->assertEquals(
            $exception->getExpectedFormat(),
            self::$testExpectedFormat
        );
    }

    /**
     * Test that we can set the expected format and get it back upon catching the exception.
     *
     * @throws InvalidFormatArgumentException Always, but it's caught.
     *
     * @return void
     */
    public function testGetExpectedFormatOnCatch()
    {
        $exception = new InvalidFormatArgumentException();
        $exception->setExpectedFormat(self::$testExpectedFormat);
        try {
            throw $exception;
        } catch (ArgumentException $e) {
            $this->assertEquals(
                $e->getExpectedFormat(),
                self::$testExpectedFormat
            );
        }
    }
}
