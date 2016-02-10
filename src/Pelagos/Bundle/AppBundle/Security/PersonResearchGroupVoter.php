<?php


namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\PersonResearchGroup;
use Pelagos\Entity\Account;
use Pelagos\Entity\FundingCycle;
use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\DataRepository;

/**
 * Class PersonResearchGroupVoter Grant or deny authority to PersonResearchGroup objects.
 *
 * This is a Symfony voter that operates only on objects of type PersonResearchGroup.
 * @see supports() to see which operations attributes are supported
 * @package Pelagos\Bundle\AppBundle\Security
 */
class PersonResearchGroupVoter extends PelagosEntityVoter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute defined in the base class that indicates the action.
     * @param mixed  $object    The subject of the operation, Must be a PersonResearchGroup.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $object)
    {
        if (!$object instanceof PersonResearchGroup) {
            return false;
        }
        if (!in_array($attribute, array(self::CAN_CREATE))) {
            return false;
        }
        return true;
    }

    /**
     * Perform a single authorization test on an attribute, ResearchGroup subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute Unused by this function but required by VoterInterface.
     * @param mixed          $object    A ResearchGroup.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True if the attribute is allowed on the subject for the user specified by the token.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        if (!$object instanceof PersonResearchGroup) {
            return false;
        }
        $user = $token->getUser();

        if (!$user instanceof Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        $fundingCycle = $object->getFundingCycle();
        if (!$fundingCycle instanceof FundingCycle) {
            if ($fundingCycle === null) {
                // check to ensure user has DRP/M role on at least one DataRepository.
                $personDataRepositories = $userPerson->getPersonDataRepositories();
                return $this->isUserDataRepositoryRole(
                    $userPerson,
                    $personDataRepositories,
                    array(self::DATA_REPOSITORY_MANAGER)
                );

            }
            return false;
        } else {
            $fundingOrganization = $fundingCycle->getFundingOrganization();
            if (!$fundingOrganization instanceof FundingOrganization) {
                return false;
            }

            $dataRepository = $fundingOrganization->getDataRepository();
            if (!$dataRepository instanceof DataRepository) {
                return false;
            }

            $personDataRepositories = $dataRepository->getPersonDataRepositories();
            return $this->isUserDataRepositoryRole(
                $userPerson,
                $personDataRepositories,
                array(self::DATA_REPOSITORY_MANAGER)
            );
        }
        return false;
    }

}