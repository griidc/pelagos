<?php

namespace App\Security\Voter;

use App\Entity\Account;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter for Accounts.
 */
class AccountVoter extends PelagosEntityVoter
{
    public const CAN_BROWSE_INCOMING_DIRECTORY = 'CAN_BROWSE_INCOMING_DIRECTORY';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // This voter only supports Accounts.
        if (!$subject instanceof Account) {
            return false;
        }

        // This voter only supports CAN_CREATE, CAN_EDIT, and CAN_BROWSE_INCOMING_DIRECTORY.
        if (!in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT, self::CAN_BROWSE_INCOMING_DIRECTORY])) {
            return false;
        }

        // If we get here, the attribute and subject are supported by this voter.
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Get the user from the authentication token.
        $user = $token->getUser();

        // If the user token does not contain an Account, deny.
        if (!$user instanceof Account) {
            return false;
        }

        // If we don't have Person for both the authenticated user and the subject, deny.
        if (null === $user->getPerson() or null === $subject->getPerson()) {
            return false;
        }

        // A Person can create and edit their own account and browse their own incoming directory.
        if (
            in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT, self::CAN_BROWSE_INCOMING_DIRECTORY])
            and $subject->getPerson()->isSameTypeAndId($user->getPerson())
        ) {
            return true;
        }

        // A DRPM can browse other people's incoming directories.
        if (
            in_array($attribute, [self::CAN_BROWSE_INCOMING_DIRECTORY])
            and in_array(Account::ROLE_DATA_REPOSITORY_MANAGER, $user->getRoles())
        ) {
            return true;
        }

        // Deny by default.
        return false;
    }
}
