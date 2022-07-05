<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Account;
use App\Entity\Person;

/**
 * A voter to determine if a actions are possible by the user on a Person object.

 * @package Pelagos\Bundle\AppBundle\Security
 */
class PersonVoter extends PelagosEntityVoter
{
    /**
     * Determine if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute denoting an action.
     * @param mixed  $object    The subject of creation, deletion or change.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $object) //phpcs:ignore
    {
        // Make sure the object is an instance of Person
        if (!$object instanceof Person) {
            return false;
        }

        // Supports PelagosEntityVoter::CAN_EDIT
        if (in_array($attribute, array(PelagosEntityVoter::CAN_EDIT))) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    /**
     * Perform a authorization test on an attribute, Person subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $object    A Person.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True If the user has one of the target roles for any of the subject's DataRepositories.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token) //phpcs:ignore
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // People can edit themselves.
        if ($attribute == PelagosEntityVoter::CAN_EDIT and $object->isSameTypeAndId($userPerson)) {
            return true;
        }

        return false;
    }
}
