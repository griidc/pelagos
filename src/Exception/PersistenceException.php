<?php

namespace App\Exception;

/**
 * Custom exception for errors related to persistence.
 */
class PersistenceException extends \Exception
{
    /**
     * The query sent to the database.
     *
     * @var string
     */
    protected $databaseQuery;

    /**
     * The parameters sent to the database.
     *
     * @var string
     */
    protected $databaseParams;

    /**
     * The SQLSTATE error code from the database.
     *
     * @var string
     */
    protected $databaseErrorCode;

    /**
     * The error code from the database driver.
     *
     * @var integer
     */
    protected $databaseErrorDriverCode;

    /**
     * The error message from the database.
     *
     * @var string
     */
    protected $databaseErrorMessage;

    /**
     * The error message from the database.
     *
     * @var string
     */
    protected $databaseErrorHint;

    /**
     * Constructor for a PersistenceException.
     *
     * If previous exception's previous exception is a PDOException, pull out
     * the database error codes, message, and hint from the PDOException.
     * Attempt to extract query and parameters from error message.
     *
     * @param string     $message  An exception message.
     * @param integer    $code     An exception code.
     * @param \Exception $previous A previous exception.
     */
    public function __construct(string $message = '', int $code = 0, \Exception $previous = null)
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
