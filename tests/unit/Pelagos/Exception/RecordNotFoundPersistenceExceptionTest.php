<?php

namespace Pelagos\Exception;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Exception\RecordNotFoundPersistenceException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\PersistenceException
 */
class RecordNotFoundPersistenceExceptionTest extends TestCase
{
    /**
     * Test that we can throw a RecordNotFoundPersistenceException.
     *
     * @expectedException \Pelagos\Exception\RecordNotFoundPersistenceException
     *
     * @throws RecordNotFoundPersistenceException Always.
     *
     * @return void
     */
    public function testThrowRecordNotFoundPersistenceException()
    {
        throw new RecordNotFoundPersistenceException();
    }

    /**
     * Test that we can catch a RecordNotFoundPersistenceException.
     *
     * @throws RecordNotFoundPersistenceException Always, but it's caught.
     *
     * @return void
     */
    public function testCatchRecordNotFoundPersistenceException()
    {
        try {
            throw new RecordNotFoundPersistenceException();
        } catch (RecordNotFoundPersistenceException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a RecordNotFoundPersistenceException as an PersistenceException.
     *
     * @throws RecordNotFoundPersistenceException Always, but it's caught as a PersistenceException.
     *
     * @return void
     */
    public function testCatchPersistenceException()
    {
        try {
            throw new RecordNotFoundPersistenceException();
        } catch (PersistenceException $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can catch a RecordNotFoundPersistenceException as an \Exception.
     *
     * @throws RecordNotFoundPersistenceException Always, but it's caught as an \Exception.
     *
     * @return void
     */
    public function testCatchException()
    {
        try {
            throw new RecordNotFoundPersistenceException();
        } catch (\Exception $e) {
            // Do nothing but exception is caught.
        }
    }

    /**
     * Test that we can set an id and get it back upon catching the exception.
     *
     * @throws RecordNotFoundPersistenceException Always, but it's caught.
     *
     * @return void
     */
    public function testGetId()
    {
        try {
            $exception = new RecordNotFoundPersistenceException();
            $exception->setId(999);
            throw $exception;
        } catch (RecordNotFoundPersistenceException $e) {
            $this->assertEquals($e->getId(), 999);
        }
    }
}
