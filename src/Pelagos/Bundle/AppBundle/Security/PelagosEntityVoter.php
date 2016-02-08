<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Pelagos\Entity\Person;
use Pelagos\Entity\PersonDataRepository;
use Pelagos\Entity\PersonResearchGroup;
use Pelagos\Entity\DataRepositoryRole;

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

    /**
     * Does this Person have one of the DataRepositoryRoles listed in roleNames?.
     *
     * @param Person $userPerson This is the logged in user's representation.
     * @param array  $roleNames  List of user roles to be tested.
     *
     * @see voteOnAttribute($attribute, $object, TokenInterface $token)
     *
     * @return bool True if the user is a manager.
     */
    protected function isUserDataRepositoryRole(Person $userPerson, array $roleNames)
    {
        $personDataRepositories = $userPerson->getPersonDataRepositories();

        if (!$personDataRepositories instanceof \Traversable) {
            return false;
        }

        foreach ($personDataRepositories as $personDR) {
            if (!$personDR instanceof PersonDataRepository) {
                continue;
            }
            //  get the role from the Person/DataRepository object
            $role = $personDR->getRole();
            if (!$role instanceof DataRepositoryRole) {
                continue;
            }
            //  what is the name
            $roleName = $role->getName();
            // what Person is in the relation to DataRepository
            $person = $personDR->getPerson();

            if ($userPerson->equals($person) && in_array($roleName, $roleNames)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Does this Person have one of the ResearchGroupRoles listed in roleNames?.
     *
     * @param Person $userPerson The logged in user's representation.
     * @param array  $roleNames  List of user roles to be tested.
     *
     * @see voteOnAttribute($attribute, $object, TokenInterface $token)
     *
     * @return bool True if the user is a manager.
     */
    protected function isUserResearchGroupRole(Person $userPerson, array $roleNames)
    {
        $personResearchGroups = $userPerson->getPersonResearchGroups();
        if (!$personResearchGroups instanceof \Traversable) {
            return false;
        }

        foreach ($personResearchGroups as $personRG) {
            if (!$personRG instanceof PersonResearchGroup) {
                continue;
            }
            //  get the role from the Person/ResearchGroup object
            $role = $personRG->getRole();
            if (!$role instanceof ResearchGroupRole) {
                continue;
            }
            //  what is the name
            $roleName = $role->getName();
            // what Person is in the relation to DataRepository
            $person = $personRG->getPerson();

            if ($userPerson->equals($person) && in_array($roleName, $roleNames)) {
                return true;
            }
        }
        return false;
    }
}
