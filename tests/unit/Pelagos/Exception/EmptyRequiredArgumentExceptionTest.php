<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\EmptyRequiredArgumentException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\ArgumentException
 */
class EmptyRequiredArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that we can throw a EmptyRequiredArgumentException.
     *
     * @expectedException \Pelagos\Exception\EmptyRequiredArgumentException
     *
     * @throws EmptyRequiredArgumentException Always.
     *
     * @return void
     */
    public function testThrowEmptyRequiredArgumentException()
    {
        throw new EmptyRequiredArgumentException();
    }

    /**
     * Test that we can catch a EmptyRequiredArgumentException.
     *
     * @throws EmptyRequiredArgumentException Always, but it is caught.
     *
     * @return void
     */
    public function testCatchEmptyRequiredArgumentException()
    {
        try {
            throw new EmptyRequiredArgumentException();
        } catch (EmptyRequiredArgumentException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a EmptyRequiredArgumentException as an ArgumentException.
     *
     * @throws EmptyRequiredArgumentException Always, but it is caught as an ArgumentException.
     *
     * @return void
     */
    public function testCatchArgumentException()
    {
        try {
            throw new EmptyRequiredArgumentException();
        } catch (ArgumentException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a EmptyRequiredArgumentException as an \Exception.
     *
     * @throws EmptyRequiredArgumentException Always, but it is caught as an \Exception.
     *
     * @return void
     */
    public function testCatchException()
    {
        try {
            throw new EmptyRequiredArgumentException();
        } catch (\Exception $e) {
            // Do nothing but exception is caught.
        }
    }
}
