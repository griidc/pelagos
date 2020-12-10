<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use App\Entity\Account;
use App\Entity\File as PelagosFile;
use App\Entity\Person;
use App\Entity\ResearchGroup;

/**
 * A voter to determine if CRUD actions possible on a File.

 * @package App\Security\Voter
 */
class FileVoter extends PelagosEntityVoter
{
    /**
     * Determine if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute denoting an action.
     * @param mixed  $subject   The subject of creation,
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $subject) //phpcs:ignore
    {
        // Make sure the subject is an instance of Dataset
        if (!$subject instanceof PelagosFile) {
            return false;
        }

        // Supports CRUD.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_READ, self::CAN_EDIT, self::CAN_DELETE))) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    /**
     * Perform a authorization test on an attribute, Dataset subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $subject   A Dataset.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True If the user has one of the target roles for any of the subject's DataRepositories.
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) //phpcs:ignore
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Return TRUE if Account is of a user who's a member of the same RG as the Dataset.
        $person = $user->getPerson();
        if ($person instanceof Person and in_array($dataset->getResearchGroup(), $person->getResearchGroups())) {
            return true;
        }

        return false;
    }
}
