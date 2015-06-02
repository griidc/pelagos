<?php

namespace Citation;

/**
 * An Exception thrown when errors are detected
 * when UDIs are used in operations
 * @see UdiValidation.php
 */
class InvalidUdiException extends \Exception
{

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
