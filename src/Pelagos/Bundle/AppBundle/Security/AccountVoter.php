<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\Account;

/**
 * A voter to allow to create an Account.
 */
class AccountVoter extends PelagosEntityVoter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute A string representing the supported attribute.
     * @param mixed  $object    An object as required by the voter interface, not used otherwise.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $object)
    {
        if ($object instanceof Account) {
            return true;
        }

        return false;
    }

    /**
     * Perform a single authorization test on an attribute, authentication token, ignored subject.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute Unused by this function but required by VoterInterface.
     * @param mixed          $object    A object required by Voter interface, ignored.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True if the attribute is allowed on the subject for the user specified by the token.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        // Anyone can create a PersonToken?
        return true;
    }
}
