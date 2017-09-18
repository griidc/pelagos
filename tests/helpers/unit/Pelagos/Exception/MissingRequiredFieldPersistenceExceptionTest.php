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
     *
     * @throws MissingRequiredFieldPersistenceException Always.
     *
     * @return void
     */
    public function testThrowMissingRequiredFieldPersistenceException()
    {
        throw new MissingRequiredFieldPersistenceException();
    }

    /**
     * Test that we can catch a MissingRequiredFieldPersistenceException.
     *
     * @throws MissingRequiredFieldPersistenceException Always, but it's caught.
     *
     * @return void
     */
    public function testCatchMissingRequiredFieldPersistenceException()
    {
        try {
            throw new MissingRequiredFieldPersistenceException();
        } catch (MissingRequiredFieldPersistenceException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a MissingRequiredFieldPersistenceException as an PersistenceException.
     *
     * @throws MissingRequiredFieldPersistenceException Always, but it's caught as a PersistenceException.
     *
     * @return void
     */
    public function testCatchPersistenceException()
    {
        try {
            throw new MissingRequiredFieldPersistenceException();
        } catch (PersistenceException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a MissingRequiredFieldPersistenceException as an \Exception.
     *
     * @throws MissingRequiredFieldPersistenceException Always, but it's caught as an \Exception.
     *
     * @return void
     */
    public function testCatchException()
    {
        try {
            throw new MissingRequiredFieldPersistenceException();
        } catch (\Exception $e) {
            // Do nothing but exception is caught.
        }
    }
}
