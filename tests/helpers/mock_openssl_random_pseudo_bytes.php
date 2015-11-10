<?php

namespace Pelagos\Entity;

/**
 * Mock PHP's built-in openssl_random_pseudo_bytes function.
 *
 * The final value of $cryptoStrong can be controlled by setting the value
 * of $GLOBALS['cryptoStrong'] to the desired value.
 *
 * WARNING: this mocked function will NEVER generate a cryptographically
 * strong byte string, but will, by default, say it did.
 * It should only ever be used for testing purposes.
 *
 * @param integer $bytes        Length of byte string to generate.
 * @param boolean $cryptoStrong Whether or not the byte string is cryptographically strong.
 *
 * @return string Binary string of length $bytes containing null bytes.
 */
function openssl_random_pseudo_bytes($bytes, &$cryptoStrong)
{
    if (array_key_exists('cryptoStrong', $GLOBALS)) {
        $cryptoStrong = $GLOBALS['cryptoStrong'];
    } else {
        $cryptoStrong = true;
    }
    return str_repeat("\0", $bytes);
}
