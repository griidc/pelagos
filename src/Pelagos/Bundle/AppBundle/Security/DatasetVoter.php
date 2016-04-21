<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\Dataset;

/**
 * A voter to determine if a actions are possible by the user on a Dataset.

 * @package Pelagos\Bundle\AppBundle\Security
 */
class DatasetVoter extends PelagosEntityVoter
{
    /**
     * Determine if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute denoting an action.
     * @param mixed  $subject   The subject of creation, deletion or change.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $subject)
    {
        // Make sure the subject is an instance of Dataset
        if (!$subject instanceof Dataset) {
            return false;
        }

        // Supports create and edit.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT))) {
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
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Anyone with an account can create or edit.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT))) {
            return true;
        }

        return false;
    }
}
