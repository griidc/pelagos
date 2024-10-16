<?php

namespace App\Security\Voter;

use App\Entity\PersonToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter for PersonTokens.
 */
class PersonTokenVoter extends PelagosEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // This voter only supports PersonTokens.
        if (!$subject instanceof PersonToken) {
            return false;
        }

        // This voter only supports attributes CAN_CREATE and CAN_DELETE.
        if (!in_array($attribute, [self::CAN_CREATE, self::CAN_DELETE])) {
            return false;
        }

        // If we get here, the attribute and subject are supported by this voter.
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Anyone can create and delete PersonTokens.
        if (in_array($attribute, [self::CAN_CREATE, self::CAN_DELETE])) {
            return true;
        }

        // Deny by default.
        return false;
    }
}
