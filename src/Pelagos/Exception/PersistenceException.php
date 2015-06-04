<?php

namespace Pelagos\Exception;

/**
 * Custom exception for errors related to persistence.
 */
class PersistenceException extends \Exception
{
    /** @var string The query sent to the database. */
    protected $databaseQuery;

    /** @var string The parameters sent to the database. */
    protected $databaseParams;

    /** @var int The error code from the database. */
    protected $databaseErrorCode;

    /** @var string The error message from the database. */
    protected $databaseErrorMessage;

    /** @var string The error message from the database. */
    protected $databaseErrorHint;

    /**
     * Constructor for a PersistenceException.
     * Pull out pieces from error message and populate properties.
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (preg_match('/An exception occurred while executing \'([^\']+)\'/', $message, $matches)) {
            $this->databaseQuery = $matches[1];
        }
        if (preg_match('/with params \[([^\]]+)\]/', $message, $matches)) {
            $this->databaseParams = $matches[1];
        }
        if (preg_match('/SQLSTATE\[(\d+)\]/', $message, $matches)) {
            $this->databaseErrorCode = $matches[1];
        }
        if (preg_match('/ERROR: +(.*?)(?:\'|\n|$)/', $message, $matches)) {
            $this->databaseErrorMessage = $matches[1];
        }
        if (preg_match('/HINT: +(.*?)(?:\'|\n|$)/', $message, $matches)) {
            $this->databaseErrorHint = $matches[1];
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Getter for databaseQuery.
     *
     * @return string The query sent to the database
     */
    public function getDatabaseQuery()
    {
        return $this->databaseQuery;
    }

    /**
     * Getter for databaseParams.
     *
     * @return string The parameters sent to the database
     */
    public function getDatabaseParams()
    {
        return $this->databaseParams;
    }

    /**
     * Getter for databaseErrorCode.
     *
     * @return int The error code from the database
     */
    public function getDatabaseErrorCode()
    {
        return $this->databaseErrorCode;
    }

    /**
     * Getter for databaseErrorMessage.
     *
     * @return string The error message from the database
     */
    public function getDatabaseErrorMessage()
    {
        return $this->databaseErrorMessage;
    }

    /**
     * Getter for databaseErrorHint.
     *
     * @return string The error hint from the database
     */
    public function getDatabaseErrorHint()
    {
        return $this->databaseErrorHint;
    }
}
