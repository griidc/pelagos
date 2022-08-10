<?php

namespace App\Util;

use Hidehalo\Nanoid\Client;

/**
 * A utility that generates a random 8 char base32 identifier.
 */
class Base32Generator
{
    /**
     * @string Alaphabet by Douglass Crockford for ease of human communication of strings over voice and
     * minimizing the possibility of generation of incidental curse words.
     */
    const CROCKFORDALPHABET = '0123456789abcdefghjkmnpqrstvwxyz';

    /**
     * @int Default length of ID to generate.
     */
    const DEFAULTLENGTH = 8;

    /**
     * Method that returns an 8 char cryptographically-sound pseudorandom string using the Crockford 32 alphabet.
     *
     * @return string
     */
    public static function generateId(): string
    {
        $client = new Client();
        return $client->formattedId(self::CROCKFORDALPHABET, $size = self::DEFAULTLENGTH);
    }
}
