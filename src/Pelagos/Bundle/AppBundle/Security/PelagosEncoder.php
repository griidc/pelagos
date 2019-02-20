<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * This is a custom password encoder.
 */
class PelagosEncoder implements PasswordEncoderInterface
{

    /**
     * Encode the password.
     *
     * @param string $raw  The raw password (plaintext).
     * @param string $salt The salt passed one from the user object (getSalt).
     *
     * @return string The encoded password.
     */
    public function encodePassword($raw, $salt)
    {
        return sha1($raw . $salt, true);
    }

    /**
     * Encode the password.
     *
     * @param string $encoded The encoded password.
     * @param string $raw     The raw password (plaintext).
     * @param string $salt    The salt passed one from the user object (getSalt).
     *
     * @return boolean True if the encoded password matches.
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded === $this->encodePassword($raw, $salt);
    }
}
