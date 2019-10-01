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
     * Setter for className.
     *
     * @param string $className Name of the class that caused the error.
     *
     * @return void
     */
    public function setClassName($className)
    {
        $this->className = $className;
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
     * Setter for propertyName.
     *
     * @param string $propertyName Name of the property that caused the error.
     *
     * @return void
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
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
