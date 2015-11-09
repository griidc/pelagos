<?php
namespace Pelagos\DBAL;

class TokenType extends EnumType
{
    const ENUM_TOKEN_TYPE = 'token_use_type';
    const CREATE_TOKEN = 'CREATE_ACCOUNT';
    const RESET_TOKEN = 'RESET_ACCOUNT';

    protected $name = self::ENUM_TOKEN_TYPE;
    protected $values = array(self::CREATE_TOKEN, self::RESET_TOKEN);
}
