<?php

namespace Pelagos\Exception;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Exception\RecordExistsPersistenceException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\PersistenceException
 */
class RecordExistsPersistenceExceptionTest extends TestCase
{
    /**
     * Test that we can throw a RecordExistsPersistenceException.
     *
     * @expectedException \Pelagos\Exception\RecordExistsPersistenceException
     *
     * @throws RecordExistsPersistenceException Always.
     *
     * @return void
     */
    public function testThrowRecordExistsPersistenceException()
    {
        throw new RecordExistsPersistenceException();
    }

    /**
     * Test that we can catch a RecordExistsPersistenceException.
     *
     * @throws RecordExistsPersistenceException Always, but it's caught.
     *
     * @return void
     */
    public function testCatchRecordExistsPersistenceException()
    {
        try {
            throw new RecordExistsPersistenceException();
        } catch (RecordExistsPersistenceException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a RecordExistsPersistenceException as an PersistenceException.
     *
     * @throws RecordExistsPersistenceException Always, but it's caught as a PersistenceException.
     *
     * @return void
     */
    public function testCatchPersistenceException()
    {
        try {
            throw new RecordExistsPersistenceException();
        } catch (PersistenceException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a RecordExistsPersistenceException as an \Exception.
     *
     * @throws RecordExistsPersistenceException Always, but it's caught as an \Exception.
     *
     * @return void
     */
    public function testCatchException()
    {
        try {
            throw new RecordExistsPersistenceException();
        } catch (\Exception $e) {
            // Do nothing but exception is caught.
        }
    }
}
