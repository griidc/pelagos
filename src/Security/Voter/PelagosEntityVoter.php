<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Doctrine\Common\Collections\Collection;
use App\Entity\Person;
use App\Entity\PersonAssociationInterface;
use App\Entity\RoleInterface;

/**
 * Class PelagosEntityVoter An abstract base class for implementing a Symfony Voter..
 *
 * Implementation of the VoterInterface is required by all voters. The VoterInterface requires
 * the implementation of the function  vote(TokenInterface $token, $subject, array $attributes).
 * The Symfony abstract class Voter implements function vote, thus satisfying VoterInterface, but
 * requires the implementation of two other functions: supports($attribute, $object).and
 * voteOnAttribute($attribute, $object, TokenInterface $token).
 * Concrete implementations of this class must implement the Voter contract.
 * Vote,implemented in this instance via the abstract class Voter, will call supports and then
 * voteOnAttribute. Success (returning true) from supports() precedes the call to voteOnAttribute.
 *
 * @package Pelagos\Bundle\AppBundle\Security
 * @see     Symfony\Component\Security\Core\Authorization\Voter\Voter
 * @see     Symfony\Component\Security\Core\Authorization\Voter\VoterInterface
 */
abstract class PelagosEntityVoter extends Voter
{
    const CAN_CREATE = 'CAN_CREATE';
    const CAN_EDIT = 'CAN_EDIT';
    const CAN_READ   = 'CAN_READ';
    const CAN_DELETE = 'CAN_DELETE';

    /**
     * Does this Person have one of the Roles listed in roleNames?.
     *
     * @param Person     $userPerson     This is the logged in user's representation.
     * @param Collection $hasRoleObjects A set of HsaRoleInterface instances.
     * @param array      $roleNames      List of user roles to be tested.
     *
     * @see voteOnAttribute($attribute, $object, TokenInterface $token)
     *
     * @return boolean True if the user has a role in the roleNames.
     */
    protected function doesUserHaveRole(Person $userPerson, Collection $hasRoleObjects, array $roleNames)
    {
        if (!$hasRoleObjects instanceof \Traversable) {
            return false;
        }

        foreach ($hasRoleObjects as $hasRole) {
            //  check to see if each object in the list implements the PersonAssociationInterface
            if (!$hasRole instanceof PersonAssociationInterface) {
                continue;
            }
            //  get the role from the object
            $role = $hasRole->getRole();
            if (!$role instanceof RoleInterface) {
                continue;
            }
            //  what is the name
            $roleName = $role->getName();
            // get the Person to which the Role refers
            $person = $hasRole->getPerson();

            // if the Role Person is the same as the $userPerson and
            // the $roleName is in the list of $roleNames return true.
            if ($userPerson->isSameTypeAndId($person) && in_array($roleName, $roleNames)) {
                return true;
            }
        }
        return false;
    }
}
