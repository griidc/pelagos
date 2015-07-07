<?php

namespace Pelagos\Exception;

/**
 * Unit tests for Pelagos\Exception\PersistenceException.
 *
 * @group Pelagos
 * @group Pelagos\Exception
 * @group Pelagos\Exception\PersistenceException
 */
class PersistenceExceptionTest extends \PHPUnit_Framework_TestCase
{
    /** @var string $databaseQuery Static class variable containing a database query */
    protected static $databaseQuery = 'INSERT INTO foobar (field1, field2, field3, foobar_number) VALUES (?, ?, ?, ?)';

    /** @var string $databaseParams Static class variable containing some database parameters */
    protected static $databaseParams = '"one", "two", "three", 123';

    /** @var string $databaseErrorCode Static class variable containing a SQLSTATE database error code */
    protected static $databaseErrorCode = 12345;

    /** @var string $databaseErrorDriverCode Static class variable containing a driver-specific database error code */
    protected static $databaseErrorDriverCode = 1;

    /** @var string $databaseErrorMessage Static class variable containing a database error message */
    protected static $databaseErrorMessage = 'You are in violation!';

    /** @var string $databaseErrorHint Static class variable containing a database error hint */
    protected static $databaseErrorHint = 'Perhaps you need to do something different?';

    /** @var string $message Class variable to hold a message to pass to PersistenceException */
    protected $message;

    /**
     * Set up tests.
     * Construct a test message from the static properties.
     */
    public function setUp()
    {
        $this->message = 'An exception occurred while executing ' .  "'" . self::$databaseQuery . "' " .
            'with params [' . self::$databaseParams . ']: ' .
            'SQLSTATE[' . self::$databaseErrorCode . ']: Unique violation: 7 ' .
            'ERROR: ' . self::$databaseErrorMessage . "\n" .
            'HINT: ' . self::$databaseErrorHint;
    }

    /**
     * Test that we can throw an PersistenceException.
     *
     * @expectedException \Pelagos\Exception\PersistenceException
     */
    public function testThrowPersistenceException()
    {
        throw new PersistenceException();
    }

    /**
     * Test that we can catch an PersistenceException.
     */
    public function testCatchPersistenceException()
    {
        try {
            throw new PersistenceException();
        } catch (PersistenceException $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test that we can catch an PersistenceException as an \Exception.
     */
    public function testCatchException()
    {
        try {
            throw new PersistenceException();
        } catch (\Exception $e) {
            // do nothing but exception is caught
        }
    }

    /**
     * Test getters return null for empty message and no previous exception.
     */
    public function testGettersReturnNullEmptyNoPrevious()
    {
        try {
            throw new PersistenceException();
        } catch (PersistenceException $e) {
            $this->assertNull($e->getDatabaseQuery());
            $this->assertNull($e->getDatabaseParams());
            $this->assertNull($e->getDatabaseErrorCode());
            $this->assertNull($e->getDatabaseErrorDriverCode());
            $this->assertNull($e->getDatabaseErrorMessage());
            $this->assertNull($e->getDatabaseErrorHint());
        }
    }

    /**
     * Test getters return null for empty message and previous exception without a previous exception.
     */
    public function testGettersReturnNullEmptyNoPreviousPrevious()
    {
        try {
            throw new PersistenceException('', 0, new \Exception);
        } catch (PersistenceException $e) {
            $this->assertNull($e->getDatabaseQuery());
            $this->assertNull($e->getDatabaseParams());
            $this->assertNull($e->getDatabaseErrorCode());
            $this->assertNull($e->getDatabaseErrorDriverCode());
            $this->assertNull($e->getDatabaseErrorMessage());
            $this->assertNull($e->getDatabaseErrorHint());
        }
    }

    /**
     * Test getters return null for empty message and previous exception with a previous exception that
     * is not a PDOException.
     */
    public function testGettersReturnNullEmptyPreviousPreviousNotPDOException()
    {
        try {
            throw new PersistenceException('', 0, new \Exception('', 0, new \Exception));
        } catch (PersistenceException $e) {
            $this->assertNull($e->getDatabaseQuery());
            $this->assertNull($e->getDatabaseParams());
            $this->assertNull($e->getDatabaseErrorCode());
            $this->assertNull($e->getDatabaseErrorDriverCode());
            $this->assertNull($e->getDatabaseErrorMessage());
            $this->assertNull($e->getDatabaseErrorHint());
        }
    }

    /**
     * Test getters return null for empty message and previous exception with a previous exception that
     * is a PDOException without errorInfo set.
     */
    public function testGettersReturnNullEmptyPreviousPreviousPDOExceptionNoErrorInfo()
    {
        try {
            throw new PersistenceException('', 0, new \Exception('', 0, new \PDOException));
        } catch (PersistenceException $e) {
            $this->assertNull($e->getDatabaseQuery());
            $this->assertNull($e->getDatabaseParams());
            $this->assertNull($e->getDatabaseErrorCode());
            $this->assertNull($e->getDatabaseErrorDriverCode());
            $this->assertNull($e->getDatabaseErrorMessage());
            $this->assertNull($e->getDatabaseErrorHint());
        }
    }

    /**
     * Test getters return expected values for an expected message.
     */
    public function testGettersReturnExpectedValues()
    {
        $mockPDOException = \Mockery::mock('\PDOException');
        $mockPDOException->errorInfo = array(
            self::$databaseErrorCode,
            self::$databaseErrorDriverCode,
            'ERROR: ' . self::$databaseErrorMessage .
            "\nHINT: " . self::$databaseErrorHint
        );
        $previousException = new \Exception('', 0, $mockPDOException);
        try {
            throw new PersistenceException($this->message, 0, $previousException);
        } catch (PersistenceException $e) {
            $this->assertEquals(self::$databaseQuery, $e->getDatabaseQuery());
            $this->assertEquals(self::$databaseParams, $e->getDatabaseParams());
            $this->assertEquals(self::$databaseErrorCode, $e->getDatabaseErrorCode());
            $this->assertEquals(self::$databaseErrorDriverCode, $e->getDatabaseErrorDriverCode());
            $this->assertEquals(self::$databaseErrorMessage, $e->getDatabaseErrorMessage());
            $this->assertEquals(self::$databaseErrorHint, $e->getDatabaseErrorHint());
        }
    }

    /**
     * Test getters return expected values for a PDO message that does not follow the:
     *   ERROR: ...
     *   HINT: ....
     * convention.
     * In this case the entire message is put into $databaseErrorMessage and $databaseErrorHint will be null.
     */
    public function testGettersReturnExpectedValuesIrregularPDOMessage()
    {
        $mockPDOException = \Mockery::mock('\PDOException');
        $mockPDOException->errorInfo = array(
            self::$databaseErrorCode,
            self::$databaseErrorDriverCode,
            self::$databaseErrorMessage
        );
        $previousException = new \Exception('', 0, $mockPDOException);
        try {
            throw new PersistenceException($this->message, 0, $previousException);
        } catch (PersistenceException $e) {
            $this->assertEquals(self::$databaseQuery, $e->getDatabaseQuery());
            $this->assertEquals(self::$databaseParams, $e->getDatabaseParams());
            $this->assertEquals(self::$databaseErrorCode, $e->getDatabaseErrorCode());
            $this->assertEquals(self::$databaseErrorDriverCode, $e->getDatabaseErrorDriverCode());
            $this->assertEquals(self::$databaseErrorMessage, $e->getDatabaseErrorMessage());
        }
    }
}
