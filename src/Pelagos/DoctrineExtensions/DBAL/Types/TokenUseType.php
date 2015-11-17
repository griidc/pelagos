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
}
