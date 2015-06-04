<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\RecordExistsPersistenceException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\PersistenceException
 */
class RecordExistsPersistenceExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that we can throw a RecordExistsPersistenceException.
     *
     * @expectedException \Pelagos\Exception\RecordExistsPersistenceException
     */
    public function testThrowRecordExistsPersistenceException()
    {
        throw new RecordExistsPersistenceException();
    }

    /**
     * Test that we can catch a RecordExistsPersistenceException.
     */
    public function testCatchRecordExistsPersistenceException()
    {
        try {
            throw new RecordExistsPersistenceException();
        } catch (RecordExistsPersistenceException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a RecordExistsPersistenceException as an PersistenceException.
     */
    public function testCatchPersistenceException()
    {
        try {
            throw new RecordExistsPersistenceException();
        } catch (PersistenceException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a RecordExistsPersistenceException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new RecordExistsPersistenceException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }
}
