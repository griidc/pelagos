<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\RecordNotFoundPersistenceException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\PersistenceException
 */
class RecordNotFoundPersistenceExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that we can throw a RecordNotFoundPersistenceException.
     *
     * @expectedException \Pelagos\Exception\RecordNotFoundPersistenceException
     */
    public function testThrowRecordNotFoundPersistenceException()
    {
        throw new RecordNotFoundPersistenceException();
    }

    /**
     * Test that we can catch a RecordNotFoundPersistenceException.
     */
    public function testCatchRecordNotFoundPersistenceException()
    {
        try {
            throw new RecordNotFoundPersistenceException();
        } catch (RecordNotFoundPersistenceException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a RecordNotFoundPersistenceException as an PersistenceException.
     */
    public function testCatchPersistenceException()
    {
        try {
            throw new RecordNotFoundPersistenceException();
        } catch (PersistenceException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch a RecordNotFoundPersistenceException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new RecordNotFoundPersistenceException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can set an id and get it back upon catching the exception.
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
