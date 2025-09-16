<?php

namespace App\DoctrineExtensions\DBAL\Types;

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
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'citext';
    }

    /**
     * Converts a value from its PHP representation to its database representation of this type.
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
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
