<?php

namespace App\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\DBALException;

/**
 * Stores and retrieves DateInterval instances as PostgreSQL interval type.
 */
class IntervalType extends Type
{
    /**
     * The Interval type name.
     *
     * @var string
     */
    const INTERVAL = 'interval';

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array            $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform         The currently used database platform.
     *
     * @return string The SQL snippet to create a column of type interval.
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'INTERVAL';
    }

    /**
     * Converts a value from its PHP representation to its database representation of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @throws DBALException When attempting to convert from anything other than a PHP DateInterval.
     *
     * @return string The database representation of the value.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }
        if (gettype($value) !== 'object' or get_class($value) !== 'DateInterval') {
            throw DBALException::notSupported('convert from non DateInterval');
        }
        return $value->format('P%yY%mM%dDT%hH%iM%sS');
    }

    /**
     * Converts a value from its database representation to its PHP representation of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @throws ConversionException When the conversion fails.
     *
     * @return \DateInterval The PHP representation of the value.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null !== $value) {
            $iso8601IntervalRegEx
                = '/^P(?=.)' .
                '(?:\d+Y|Y)?' .
                '(?:\d+M|M)?' .
                '(?:\d+D|D)?' .
                '(?:T(?=.)' .
                    '(?:\d+H|H)?' .
                    '(?:\d+M|M)?' .
                    '(?:\d+' .
                        '(?:\.\d{1,2})?S|S' .
                    ')?' .
                ')?' .
                '$/';
            if (false === preg_match($iso8601IntervalRegEx, $value)) {
                throw ConversionException::conversionFailedFormat(
                    $value,
                    $this->getName(),
                    $iso8601IntervalRegEx
                );
            }
            try {
                $value = new \DateInterval($value);
            } catch (\Exception $e) {
                throw ConversionException::conversionFailed(
                    $value,
                    $this->getName()
                );
            }
        }
        return $value;
    }

    /**
     * Gets the name of this type.
     *
     * @return string The name of this type.
     */
    public function getName()
    {
        return self::INTERVAL;
    }
}
