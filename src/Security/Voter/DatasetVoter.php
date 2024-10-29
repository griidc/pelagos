<?php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\Dataset;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter to determine if a actions are possible by the user on a Dataset.
 */
class DatasetVoter extends PelagosEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Make sure the subject is an instance of Dataset
        if (!$subject instanceof Dataset) {
            return false;
        }

        // Supports create and edit.
        if (in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT])) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Anyone with an account can create or edit.
        if (in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT])) {
            return true;
        }

        return false;
    }
}
