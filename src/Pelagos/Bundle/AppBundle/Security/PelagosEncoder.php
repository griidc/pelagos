<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class PelagosEncoder implements PasswordEncoderInterface
{

    public function encodePassword($raw, $salt)
    {
        return sha1($raw . $salt, true);
    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded === $this->encodePassword($raw, $salt);
    }

}