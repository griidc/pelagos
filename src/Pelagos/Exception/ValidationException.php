<?php
/**
 * This file contains the implementation of \Pelagos\Exception\ValidationExcpetion.
 *
 * @package    Pelagos\Exception
 * @subpackage ValidationException
 */

namespace Pelagos\Exception;

/**
 * Custom exception to be thrown when validation fails.
 */
class ValidationException extends \Exception
{
    /**
     * Name of the argument that caused the error.
     *
     * @var array
     */
    protected $violations = array();

    /**
     * Construct a ValidationException.
     *
     * Constructs a ValidationException and sets violations.
     *
     * @param mixed      $violations An array or array-like object of validation violations.
     * @param string     $message    The Exception message to throw.
     * @param integer    $code       The Exception code.
     * @param \Exception $previous   The previous exception used for the exception chaining.
     */
    public function __construct($violations = array(), $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setViolations($violations);
    }

    /**
     * Setter for violations.
     *
     * @param mixed $violations Array or array-like object of violations.
     *
     * @return void
     */
    public function setViolations($violations)
    {
        $this->violations = $violations;
    }

    /**
     * Getter for violations.
     *
     * @return array Array of violations.
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
