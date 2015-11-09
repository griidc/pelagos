<?php
namespace Pelagos\DBAL;

const ENUM_TOKEN_TYPE = 'token_use_type';
const CREATE_TOKEN = 'CREATE_ACCOUNT';
const RESET_TOKEN = 'RESET_ACCOUNT';

class TokenType extends EnumType
{
    protected $name = ENUM_TOKEN_TYPE;
    protected $values = array(CREATE_TOKEN, RESET_TOKEN);
}
