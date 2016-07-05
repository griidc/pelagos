<?php
namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pelagos\Entity\Account;
use Pelagos\Entity\DoiRequest;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;

/**
 * A voter to determine if a actions are possible by the user on a DIF object.

 * @package Pelagos\Bundle\AppBundle\Security
 */
class DoiRequestVoter extends PelagosEntityVoter
{
    /**
     * These attributes represent actions that the voter may be asked about.
     */
    const CAN_APPROVE = 'CAN_APPROVE';

    /**
     * Determine if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute denoting an action.
     * @param mixed  $object    The subject of creation, deletion or change.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $object)
    {
        // Make sure the object is an instance of DoiRequest
        if (!$object instanceof DoiRequest) {
            return false;
        }

        // Supports create, edit, submit, approve, reject, unlock, and request unlock.
        if (in_array(
            $attribute,
            array(
                self::CAN_CREATE,
                self::CAN_EDIT,
                self::CAN_APPROVE,
            )
        )) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    /**
     * Perform a authorization test on an attribute, DIF subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $object    A DIF.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True If the user has one of the target roles for any of the subject's DataRepositories.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        $userPerson = $user->getPerson();
        
        $personDataRepositories = $userPerson->getPersonDataRepositories()->filter(
            function ($personDataRepository) use ($object) {
                return (!$personDataRepository->isSameTypeAndId($object));
            }
        );
  
        // Data Repository Managers can submit, approve, reject, and unlock
        if ($this->doesUserHaveRole(
            $userPerson,
            $personDataRepositories,
            array(DataRepositoryRoles::MANAGER)
        ) and in_array(
            $attribute,
            array(
                self::CAN_APPROVE,
            )
        )) {
            return true;
        }

        // Anyone can create.
        if (in_array($attribute, array(self::CAN_CREATE, ))) {
            return true;
        }

        return false;
    }
}
