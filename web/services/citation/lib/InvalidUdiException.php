<?php
/**
 * User: jvh
 * Date: 4/2/15
 * Time: 3:39 PM
 *
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

    public function InvalidUdiException($string)
    {
        self::__construct($string);
    }
}