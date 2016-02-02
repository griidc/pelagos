<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use Pelagos\Entity\Account;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\FundingCycle;
use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\DataRepository;
use Pelagos\Entity\PersonDataRepository;
use Pelagos\Entity\DataRepositoryRole;
use Pelagos\Entity\Person;

/**
 * A voter to determine if a ResearchGroup can be created.
 */
class CreateRGVoter extends Voter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute.
     * @param mixed  $object    The subject to secure.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $object)
    {
        if ($attribute != 'CAN_CREATE') {
            return false;
        }

        if (!$object instanceof ResearchGroup) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string         $attribute An attribute.
     * @param mixed          $object    The subject to secure.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True if the attribute is allowed on the subject for the user specified by the token.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        $fundingCycle = $object->getFundingCycle();
        if (!$fundingCycle instanceof FundingCycle) {
            if ($fundingCycle === null) {
                // check to see if this is an attempt to check for CAN_CREATE
                if ($attribute == 'CAN_CREATE') {
                    // check to ensure user has DRP/M role on at least one DataRepository.
                    $personDataRepositories = $userPerson->getPersonDataRepositories();
                    return $this->isUserAManager($userPerson, $personDataRepositories);
                }
            } else {
                return false;
            }
        }

        $fundingOrganization = $fundingCycle->getFundingOrganization();
        if (!$fundingOrganization instanceof FundingOrganization) {
            return false;
        }

        $dataRepository = $fundingOrganization->getDataRepository();
        if (!$dataRepository instanceof DataRepository) {
            return false;
        }

        $personDataRepositories = $dataRepository->getPersonDataRepositories();
        return $this->isUserAManager($userPerson, $personDataRepositories);
    }

    /**
     * Search the tree to find out if the User/Person is a manager.
     *
     * @param Person $userPerson             This is the logged in user's representation.
     * @param mixed  $personDataRepositories List of data repositories the user is associated with.
     * @see voteOnAttribute
     *
     * @return bool True if the user is a manager.
     */
    private function isUserAManager(Person $userPerson, $personDataRepositories)
    {
        if (!$personDataRepositories instanceof \Traversable) {
            return false;
        }

        foreach ($personDataRepositories as $personDR) {
            if (!$personDR instanceof PersonDataRepository) {
                continue;
            }
            $role = $personDR->getRole();
            if (!$role instanceof DataRepositoryRole) {
                continue;
            }
            $roleName = $role->getName();
            $person = $personDR->getPerson();

            if ($userPerson === $person and in_array($roleName, array('Manager'))) {
                return true;
            }
        }
        return false;
    }
}
