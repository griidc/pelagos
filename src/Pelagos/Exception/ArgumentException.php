<?php

namespace Pelagos\Exception;

/**
 * Custom exception for errors related to arguments.
 */
class ArgumentException extends \Exception
{
    /**
     * Name of the argument that caused the error.
     *
     * @var string $argumentName
     */
    protected $argumentName;

    /**
     * Value of the argument that caused the error.
     *
     * @var mixed $argumentValue
     */
    protected $argumentValue;

    /**
     * Setter for argumentName.
     *
     * @param string $argumentName Name of the argument that caused the error.
     *
     * @return void
     */
    public function setArgumentName($argumentName)
    {
        $this->argumentName = $argumentName;
    }

    /**
     * Getter for argumentName.
     *
     * @return string Name of the argument that caused the error.
     */
    public function getArgumentName()
    {
        return $this->argumentName;
    }

    /**
     * Setter for argumentValue.
     *
     * @param string $argumentValue Value of the argument that caused the error.
     *
     * @return void
     */
    public function setArgumentValue($argumentValue)
    {
        $this->argumentValue = $argumentValue;
    }

    /**
     * Getter for argumentValue.
     *
     * @return string Value of the argument that caused the error.
     */
    public function getArgumentValue()
    {
        return $this->argumentValue;
    }
}
