<?php

namespace App\Enum;

/**
 * Types for Keywords.
 */
enum KeywordType: string
{
    case TYPE_ANZSRC = 'anzsrc';
    case TYPE_GCMD = 'gcmd';
    case TYPE_GCMD_SCIENCE = 'gcmd:science';
    case TYPE_GCMD_DISCIPLINE = 'gcmd:discipline';

    static public function fromString(?string $keyword): ?KeywordType
    {
        switch ($keyword)
        {
            case 'anzsrc':
                return KeywordType::TYPE_ANZSRC;
                break;
            case 'gcmd':
                return KeywordType::TYPE_GCMD;
                break;
            case 'gcmd:science':
                return KeywordType::TYPE_GCMD_SCIENCE;
                break;
            case 'gcmd:discipline':
                return KeywordType::TYPE_GCMD_DISCIPLINE;
                break;
            default:
                return null;
        }
    }
}
