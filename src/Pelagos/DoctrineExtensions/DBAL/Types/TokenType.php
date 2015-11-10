<?php
namespace Pelagos\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * TokenType class implementation of EnumeratedType base class.
 *
 * @see EnumType
 */
class TokenUseType extends EnumType
{
   /**
    * Enumeration's name.
    *
    * @var string ENUM_TOKEN_TYPE
    */
    const ENUM_TOKEN_TYPE = 'token_use_type';

   /**
    * Enumeration value.
    *
    * @var string CREATE_TOKEN
    */
    const CREATE_TOKEN = 'CREATE_ACCOUNT';

   /**
    * Enumeration's value.
    *
    * @var string RESET_ACCOUNT
    */
    const RESET_TOKEN = 'RESET_ACCOUNT';

   /**
    * Enumeration's name as used by this class.
    *
    * @var string name
    */
    protected $name = self::ENUM_TOKEN_TYPE;

   /**
    * Enumeration's values.
    *
    * @var array
    */
    protected $values = array(self::CREATE_TOKEN, self::RESET_TOKEN);

    /**
     * Converts a value from its PHP representation to its database representation of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @throws \InvalidArgumentException   When attempting convert a non-enumerated value.
     *
     * @return string The database representation of the value.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // pass null values through and do no further checks.  Useful in case a null is allowed in DB.
        if ($value === null) {
            return null;
        }
        if (!in_array($value, $this->values)) {
            throw new \InvalidArgumentException("Invalid '" . $this->name . "' value.");
        }
        return $value;
    }
}
