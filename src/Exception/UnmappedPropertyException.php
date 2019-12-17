<?php

namespace App\Exception;

/**
 * Custom exception for errors related to unmapped properties.
 */
class UnmappedPropertyException extends \Exception
{
    /**
     * Name of the class that caused the error.
     *
     * @var string
     */
    protected $className;

    /**
     * Name of the property that caused the error.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * Constructor for the class.
     *
     * @param string $className    Name of the class that caused the error.
     * @param string $propertyName Name of the property that caused the error.
     */
    public function __construct(string $className, string $propertyName)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;
    }

    /**
     * Getter for className.
     *
     * @return string Name of the class that caused the error.
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Getter for propertyName.
     *
     * @return string Name of the property that caused the error.
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }
}
