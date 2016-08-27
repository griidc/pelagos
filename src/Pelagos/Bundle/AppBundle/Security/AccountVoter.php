<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\Account;

/**
 * A voter for Accounts.
 */
class AccountVoter extends PelagosEntityVoter
{
    const CAN_BROWSE_INCOMING_DIRECTORY = 'CAN_BROWSE_INCOMING_DIRECTORY';

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
        // This voter only supports Accounts.
        if (!$subject instanceof Account) {
            return false;
        }

        // This voter only supports CAN_CREATE, CAN_EDIT, and CAN_BROWSE_INCOMING_DIRECTORY.
        if (!in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_BROWSE_INCOMING_DIRECTORY))) {
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
        // Get the user from the authentication token.
        $user = $token->getUser();

        // If the user token does not contain an Account, deny.
        if (!$user instanceof Account) {
            return false;
        }

        // If we don't have Person for both the authenticated user and the subject, deny.
        if ($user->getPerson() === null or $subject->getPerson() === null) {
            return false;
        }

        // A Person can create and edit their own account and browse their own incoming directory.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_BROWSE_INCOMING_DIRECTORY)) and
            $subject->getPerson()->isSameTypeAndId($user->getPerson())) {
            return true;
        }

        // A DRPM can browse other people's incoming directories.
        if (in_array($attribute, array(self::CAN_BROWSE_INCOMING_DIRECTORY)) and
            in_array(Account::ROLE_DATA_REPOSITORY_MANAGER, $user->getRoles())) {
            return true;
        }

        // Deny by default.
        return false;
    }
}
