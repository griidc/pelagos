<?php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\PersonToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter for PersonTokens.
 */
class PersonTokenVoter extends PelagosEntityVoter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute The attribute being checked.
     * @param mixed  $subject   The subject being voted on.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    // Next line to be ignored because implemented function does not have type-hint on $attribute.
    // phpcs:ignore
    protected function supports($attribute, $subject)
    {
        // This voter only supports PersonTokens.
        if (!$subject instanceof PersonToken) {
            return false;
        }

        // This voter only supports attributes CAN_CREATE and CAN_DELETE.
        if (!in_array($attribute, array(self::CAN_CREATE, self::CAN_DELETE))) {
            return false;
        }

        // If we get here, the attribute and subject are supported by this voter.
        return true;
    }

    /**
     * Perform a single authorization test on an attribute, authentication token, ignored subject.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The attribute being checked.
     * @param mixed          $subject   The subject being voted on.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True if the attribute is allowed on the subject for the user specified by the token.
     */
    // Next line to be ignored because implemented function does not have type-hint on $attribute.
    // phpcs:ignore
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        // Anyone can create and delete PersonTokens.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_DELETE))) {
            return true;
        }

        // Deny by default.
        return false;
    }
}
