<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Account;
use App\Entity\Person;

/**
 * A voter to determine if a actions are possible by the user on a Person subject.
 */
class PersonVoter extends PelagosEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Make sure the subject is an instance of Person
        if (!$subject instanceof Person) {
            return false;
        }

        // Supports PelagosEntityVoter::CAN_EDIT
        if (in_array($attribute, array(PelagosEntityVoter::CAN_EDIT))) {
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

        // Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // People can edit themselves.
        if ($attribute == PelagosEntityVoter::CAN_EDIT and $subject->isSameTypeAndId($userPerson)) {
            return true;
        }

        return false;
    }
}
