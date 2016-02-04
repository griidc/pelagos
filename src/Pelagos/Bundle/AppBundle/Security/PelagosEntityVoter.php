<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use \Doctrine\Common\Collections\Collection;

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
 * @see Symfony\Component\Security\Core\Authorization\Voter\Voter
 * @see Symfony\Component\Security\Core\Authorization\Voter\VoterInterface
 */
abstract class PelagosEntityVoter extends Voter
{
    /**
     * Class constant that identify user manager role.
     * @var string DATA_REPOSITORY_MANAGER
     */
    const DATA_REPOSITORY_MANAGER = 'Manager';

    /**
     * Class constant identifying create authority.
     * @var string CAN_CREATE
     */
    const CAN_CREATE = 'CAN_CREATE';

    /**
     * Class constant identifying edit authority.
     * @var string CAN_EDIT
     */
    const CAN_EDIT = 'CAN_EDIT';

    private static $supportedActions = array(self::CAN_CREATE, self::CAN_EDIT);

    /**
     * Determines if the attribute is one of those known to this voter.
     *
     * @param string $attribute A candidate attribute.
     *
     * @return boolean True if the attribute is one supported by this voter, false otherwise.
     */
    protected function supportsAttribute($attribute)
    {
        if (!in_array($attribute, self::$supportedActions)) {
            return false;
        }
        return true;
    }

    /**
     * Traverse the tree to find out if the User/Person is a manager.
     *
     * @param Person     $userPerson             This is the logged in user's representation.
     * @param Collection $personDataRepositories List of data repositories the user is associated with.
     *
     * @see voteOnAttribute($attribute, $object, TokenInterface $token)
     *
     * @return bool True if the user is a manager.
     */
    protected function isUserDataRepositoryRole(Person $userPerson, Collection $personDataRepositories, $roleNames)
    {
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
            //  what is the name name
            $roleName = $role->getName();
            // what Person is in the relation to DataRepository
            $person = $personDR->getPerson();

            if ($userPerson === $person and in_array($roleName, $roleNames)) {
                return true;
            }
        }
        return false;
    }
}
