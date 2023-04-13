<?php

namespace App\Enum;

use BackedEnum;

/**
 * Types for Keywords.
 */
enum KeywordType: string implements BackedEnum
{
    case TYPE_ANZSRC = 'anzsrc';
    case TYPE_GCMD = 'gcmd';

    static public function tryFrom(int|string $keyword): ?static
    {
        return match ($keyword) {
            KeywordType::TYPE_ANZSRC->value => KeywordType::TYPE_ANZSRC,
            KeywordType::TYPE_GCMD->value => KeywordType::TYPE_GCMD,
            default => null,
        };
    }
}
