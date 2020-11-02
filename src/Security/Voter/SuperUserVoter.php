<?php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\DataRepositoryRole;
use App\Entity\Entity;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter to allow Super Users to do anything.
 */
class SuperUserVoter extends PelagosEntityVoter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute A string representing the supported attribute.
     * @param mixed  $object    An object as required by the voter interface, not used otherwise.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    // Next line to be ignored because implemented function does not have type-hint on $attribute.
    // phpcs:ignore
    protected function supports($attribute, $object)
    {
        if (!$object instanceof Entity) {
            return false;
        }
        if (!in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE))) {
            return false;
        }
        return true;
    }

    /**
     * Perform a single authorization test on an attribute, authentication token, ignored subject.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute Unused by this function but required by VoterInterface.
     * @param mixed          $object    A object required by Voter interface, ignored.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True if the attribute is allowed on the subject for the user specified by the token.
     */
    // Next line to be ignored because implemented function does not have type-hint on $attribute.
    // phpcs:ignore
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        $personDataRepositories = $userPerson->getPersonDataRepositories()->filter(
            function ($personDataRepository) use ($object) {
                return (!$personDataRepository->isSameTypeAndId($object));
            }
        );
        // Data Repository Managers are Super Users
        if (
            $this->doesUserHaveRole(
                $userPerson,
                $personDataRepositories,
                array(DataRepositoryRole::MANAGER)
            )
            and in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE))
        ) {
            return true;
        }
        return false;
    }
}
