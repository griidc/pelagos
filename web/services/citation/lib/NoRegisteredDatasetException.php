<?php
// @codingStandardsIgnoreFile

namespace Citation;

/**
 * An Exception thrown when the UDI is valid
 * but there are no registered datasets
 * @see Dataset.php
 */
class NoRegisteredDatasetException extends \Exception
{

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
