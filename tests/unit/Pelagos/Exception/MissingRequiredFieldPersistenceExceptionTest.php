<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\MissingRequiredFieldPersistenceException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\PersistenceException
 */
class MissingRequiredFieldPersistenceExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that we can throw a MissingRequiredFieldPersistenceException.
     *
     * @expectedException \Pelagos\Exception\MissingRequiredFieldPersistenceException
     */
    public function testThrowMissingRequiredFieldPersistenceException()
    {
        throw new MissingRequiredFieldPersistenceException();
    }

    /**
     * Test that we can catch a MissingRequiredFieldPersistenceException.
     */
    public function testCatchMissingRequiredFieldPersistenceException()
    {
        try {
            throw new MissingRequiredFieldPersistenceException();
        } catch (MissingRequiredFieldPersistenceException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a MissingRequiredFieldPersistenceException as an PersistenceException.
     */
    public function testCatchPersistenceException()
    {
        try {
            throw new MissingRequiredFieldPersistenceException();
        } catch (PersistenceException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a MissingRequiredFieldPersistenceException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new MissingRequiredFieldPersistenceException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }
}
