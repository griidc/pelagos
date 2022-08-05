<?php

namespace App\Util;

use Hidehalo\Nanoid\Client;
use Hidehalo\Nanoid\GeneratorInterface;

/**
 * A utility that generates a random 8 char base32 identifier.
 */
class Base32Generator
{
    const CROCKFORDALPHABET='0123456789abcdefghjkmnpqrstvwxyz';
    public static function generateId(): string
    {
        $client = new Client();
        return $client->formattedId(self::CROCKFORDALPHABET, $size=8);
    }
}
