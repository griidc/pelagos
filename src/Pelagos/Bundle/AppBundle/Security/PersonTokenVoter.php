<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\PersonToken;

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
