<?php

namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\DBALException;

/**
 * A new type of Case Insensitive Text, citext.
 */
class CITextType extends Type
{
    /**
     * The citext type name.
     *
     * @var string
     */
    const CITEXT = 'citext';

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array            $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform         The currently used database platform.
     *
     * @return string The SQL snippet to create a column of type citype.
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'citext';
    }

    /**
     * Converts a value from its PHP representation to its database representation of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @throws DBALException::notSupported When attempting to convert from anything
     *                                     other than a PHP DateInterval.
     *
     * @return string The database representation of the value.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // pass null values through and do no further checks.  Useful in case a null is allowed in DB.
        if ($value === null) {
            return null;
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
        return self::CITEXT;
    }
}
