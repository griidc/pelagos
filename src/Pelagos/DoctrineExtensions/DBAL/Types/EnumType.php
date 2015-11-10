<?php
namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * This abstract class adds a generic enumerated data type.
 */
abstract class EnumType extends Type
{
    /**
     * Type name.
     *
     * @var string
     */
    protected $name;

    /**
     * Each distinct value of the enumeration.
     *
     * @var array
     */
    protected $values = array();

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
        // prevent coding standards violation
        $griidcDummy = $fieldDeclaration;
        $griidcDummy = $platform;

        $values = array_map(
            function ($val) {
                return "'" . $val . "'";
            },
            $this->values
        );

        return 'ENUM(' . implode(', ', $values) . ") COMMENT '(DC2Type:" . $this->name . ")'";
    }

    /**
     * Converts a value from its PHP representation to its database representation of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @throws \InvalidArgumentException When attempting convert a non-enumerated value.
     *
     * @return string The database representation of the value.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // prevent coding standards violation
        $griidcDummy = $platform;

        if (!in_array($value, $this->values)) {
            throw new \InvalidArgumentException("Invalid '" . $this->name . "' value.");
        }
        return $value;
    }

    /**
     * Converts a value from its database representation to its PHP representation of this type.
     *
     * @param string           $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return mixed The PHP representation of the value.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * Gets the name of this type.
     *
     * @return string The name of this type.
     */
    public function getName()
    {
        return $this->name;
    }
}
