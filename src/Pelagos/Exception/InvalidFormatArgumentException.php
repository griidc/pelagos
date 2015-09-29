<?php

namespace Pelagos\Exception;

/**
 * Custom exception for errors related to invalidly formatted arguments.
 */
class InvalidFormatArgumentException extends ArgumentException
{
    /**
     * The expected format of the argument that caused the error.
     *
     * @var string
     */
    protected $expectedFormat;

    /**
     * Setter for expectedFormat.
     *
     * @param string $expectedFormat The expected format of the argument that caused the error.
     *
     * @return void
     */
    public function setExpectedFormat($expectedFormat)
    {
        $this->expectedFormat = $expectedFormat;
    }

    /**
     * Getter for expectedFormat.
     *
     * @return string The expected format of the argument that caused the error.
     */
    public function getExpectedFormat()
    {
        return $this->expectedFormat;
    }
}
