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
    /** @var string $testExpectedFormat Static class variable containing an expected format to use for testing */
    protected static $testExpectedFormat = 'yyyy-mm-dd';

    /**
     * Test that we can throw an InvalidFormatArgumentException.
     *
     * @expectedException \Pelagos\Exception\InvalidFormatArgumentException
     */
    public function testThrowInvalidFormatArgumentException()
    {
        throw new InvalidFormatArgumentException();
    }

    /**
     * Test that we can catch a InvalidFormatArgumentException.
     */
    public function testCatchInvalidFormatArgumentException()
    {
        try {
            throw new InvalidFormatArgumentException();
        } catch (InvalidFormatArgumentException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a InvalidFormatArgumentException as an ArgumentException.
     */
    public function testCatchArgumentException()
    {
        try {
            throw new InvalidFormatArgumentException();
        } catch (ArgumentException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a InvalidFormatArgumentException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new InvalidFormatArgumentException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can set the expected format and get it back.
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
