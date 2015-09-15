<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\ValidationException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\ValidationException
 */
class ValidationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Static class variable containing a list of violationse to use for testing.
     *
     * @var string $testViolation
     */
    protected static $testViolations = array(1,2,3);

    /**
     * Test that we can throw an ValidationException.
     *
     * @expectedException \Pelagos\Exception\ValidationException
     */
    public function testThrowValidationException()
    {
        throw new ValidationException();
    }

    /**
     * Test that we can catch an ValidationException.
     */
    public function testCatchValidationException()
    {
        try {
            throw new ValidationException();
        } catch (ValidationException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch an ValidationException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new ValidationException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we construct with a violations list and get it back.
     */
    public function testConstructWithViolations()
    {
        $exception = new ValidationException(self::$testViolations);
        $this->assertEquals(
            $exception->getViolations(),
            self::$testViolations
        );
    }

    /**
     * Test that we can get the violations list upon catching the exception.
     */
    public function testGetViolationsOnCatch()
    {
        $exception = new ValidationException(self::$testViolations);
        try {
            throw $exception;
        } catch (ValidationException $e) {
            $this->assertEquals(
                $e->getViolations(),
                self::$testViolations
            );
        }
    }

    /**
     * Test that we can set the violations list and get it back.
     */
    public function testSetViolations()
    {
        $exception = new ValidationException();
        $exception->setViolations(self::$testViolations);
        $this->assertEquals(
            $exception->getViolations(),
            self::$testViolations
        );
    }
}
