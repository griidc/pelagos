<?php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\DataRepositoryRole;
use App\Entity\Entity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter to allow Super Users to do anything.
 */
class SuperUserVoter extends PelagosEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof Entity) {
            return false;
        }
        if (!in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        $personDataRepositories = $userPerson->getPersonDataRepositories()->filter(
            function ($personDataRepository) use ($subject) {
                return !$personDataRepository->isSameTypeAndId($subject);
            }
        );
        // Data Repository Managers are Super Users
        if (
            $this->doesUserHaveRole(
                $userPerson,
                $personDataRepositories,
                [DataRepositoryRole::MANAGER]
            ) and in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE])
        ) {
            return true;
        }

        return false;
    }
}
