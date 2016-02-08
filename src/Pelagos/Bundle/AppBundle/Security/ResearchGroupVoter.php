<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\FundingCycle;
use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\DataRepository;

/**
 * A voter to determine if a ResearchGroup can be created or edited.
 */
class ResearchGroupVoter extends PelagosEntityVoter
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
        if (!$object instanceof ResearchGroup) {
            return false;
        }
        if (!in_array($attribute, array(self::CAN_EDIT))) {
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
        if (!$object instanceof ResearchGroup) {
            return false;
        }
        $user = $token->getUser();

        if (!$user instanceof Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        $fundingCycle = $object->getFundingCycle();
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
        return false;
    }
}
