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

    /** @var string The SQLSTATE error code from the database. */
    protected $databaseErrorCode;

    /** @var int The error code from the database driver. */
    protected $databaseErrorDriverCode;

    /** @var string The error message from the database. */
    protected $databaseErrorMessage;

    /** @var string The error message from the database. */
    protected $databaseErrorHint;

    /**
     * Constructor for a PersistenceException.
     * If previous exception's previous exception is a PDOException, pull out
     * the database error codes, message, and hint from the PDOException.
     * Attempt to extract query and parameters from error message.
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        if (isset($previous) and is_a($previous->getPrevious(), '\PDOException')) {
            $pdoException = $previous->getPrevious();
            if (isset($pdoException->errorInfo) and is_array($pdoException->errorInfo)) {
                $this->databaseErrorCode = $pdoException->errorInfo[0];
                $this->databaseErrorDriverCode = $pdoException->errorInfo[1];
                $dbMessage = $pdoException->errorInfo[2];
                if (preg_match('/ERROR: +(.*?)(?:\n|$)/', $dbMessage, $matches)) {
                    $this->databaseErrorMessage = $matches[1];
                } else {
                    $this->databaseErrorMessage = $dbMessage;
                }
                if (preg_match('/HINT: +(.*?)(?:\n|$)/', $dbMessage, $matches)) {
                    $this->databaseErrorHint = $matches[1];
                }
            }
        }
        if (preg_match('/An exception occurred while executing \'([^\']+)\'/', $message, $matches)) {
            $this->databaseQuery = $matches[1];
        }
        if (preg_match('/with params \[([^\]]+)\]/', $message, $matches)) {
            $this->databaseParams = $matches[1];
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
     * @return string The SQLSTATE error code from the database.
     */
    public function getDatabaseErrorCode()
    {
        return $this->databaseErrorCode;
    }

    /**
     * Getter for databaseErrorDriverCode.
     *
     * @return string The driver-specific error code from the database.
     */
    public function getDatabaseErrorDriverCode()
    {
        return $this->databaseErrorDriverCode;
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
