<?php

namespace Pelagos\Exception;

/**
 * Class InvalidMetadataException.
 *
 * An exception to be thrown when text
 * expected to be XML does not validate.
 *
 * @package Exception
 */
class InvalidMetadataException extends \Exception
{
    /**
     * The array or errors.
     *
     * @var array
     */
    protected $errors;

    /**
     * Constructor for the class.
     *
     * @param array $errors Array of errors.
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Returns the array or errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
