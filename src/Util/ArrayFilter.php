<?php

namespace App\Util;

class ArrayFilter
{
    /**
     * Filters an array by removing blank values.
     */
    public static function filterArrayBlanks(array $arrayWithBlanks): array
    {
        return array_values(array_filter($arrayWithBlanks, fn($var) => strlen($var ?? '') > 0));
    }
}
