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
     */
    public function testThrowEmptyRequiredArgumentException()
    {
        throw new EmptyRequiredArgumentException();
    }

    /**
     * Test that we can catch a EmptyRequiredArgumentException.
     */
    public function testCatchEmptyRequiredArgumentException()
    {
        try {
            throw new EmptyRequiredArgumentException();
        } catch (EmptyRequiredArgumentException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a EmptyRequiredArgumentException as an ArgumentException.
     */
    public function testCatchArgumentException()
    {
        try {
            throw new EmptyRequiredArgumentException();
        } catch (ArgumentException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a EmptyRequiredArgumentException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new EmptyRequiredArgumentException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }
}
