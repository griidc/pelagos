<?php

namespace App\Util;

use App\Entity\Account;
use App\Entity\Person;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Utility class for Person-related operations.
 */
class PersonUtil
{
    public static function getPersonFromUser(?UserInterface $user): ?Person
    {
        return ($user instanceof Account) ? $user->getPerson() : null;
    }
}
