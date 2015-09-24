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
     *
     * @throws ValidationException Always.
     *
     * @return void
     */
    public function testThrowValidationException()
    {
        throw new ValidationException();
    }

    /**
     * Test that we can catch an ValidationException.
     *
     * @throws ValidationException Always, but it is caught.
     *
     * @return void
     */
    public function testCatchValidationException()
    {
        try {
            throw new ValidationException();
        } catch (ValidationException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch an ValidationException as an \Exception.
     *
     * @throws ValidationException Always, but it is caught as an \Exception.
     *
     * @return void
     */
    public function testCatchException()
    {
        try {
            throw new ValidationException();
        } catch (\Exception $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we construct with a violations list and get it back.
     *
     * @return void
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
     *
     * @throws ValidationException Always, but it is caught.
     *
     * @return void
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
     *
     * @return void
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
