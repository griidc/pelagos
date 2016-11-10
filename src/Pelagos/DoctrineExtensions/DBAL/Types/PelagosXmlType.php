<?php

namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\PostgresTypes\XmlType;

/**
 * Extension of OpenSoft's DoctrineExtensions XmlType.
 */
class PelagosXmlType extends XmlType
{
    /**
     * Modifies the SQL expression (identifier, parameter) to convert to a PHP value.
     *
     * This overrides the parent method and casts xml fields to text.
     *
     * @param string $value    The value to convert.
     * @param mixed  $platform The DB platform. This is an AbtractPlatform, but has to match
     *                         parent function signature so can't use type hint here.
     *
     * @return string
     */
    public function convertToPHPValueSQL($value, $platform)
    {
        return "$value::text";
    }
}
