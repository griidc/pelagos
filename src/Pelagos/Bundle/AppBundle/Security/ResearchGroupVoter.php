<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;
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
        // If this isn't a CAN_EDIT attribute, we cannot vote.
        if (!in_array($attribute, array(self::CAN_EDIT))) {
            return false;
        }

        // Make sure the object is an instance of ResearchGroup
        if (!$object instanceOf ResearchGroup) {
            return false;
        }

        // Only if the tree is as expected, vote.
        if (($object
                ->getFundingCycle() instanceof FundingCycle) and
            ($object
                ->getFundingCycle()
                ->getFundingOrganization()
                instanceof FundingOrganization) and
            ($object
                ->getFundingCycle()
                ->getFundingOrganization()
                ->getDataRepository()
                instanceof DataRepository)
        ) {
            return true;
        }
        // Otherwise abstain.
        return false;
    }

    /**
     * Perform a authorization test on an attribute, ResearchGroup subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $object    A ResearchGroup.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True if the attribute (action) is allowed on the subject for the user specified by the token.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // These people are allowed to edit ResearchGroups.
        if ($attribute == PelagosEntityVoter::CAN_EDIT) {
            // Data Repository Person - Manager (aka DR-P/M)
            $personDataRepositories = $object
                                          ->getFundingCycle()
                                          ->getFundingOrganization()
                                          ->getDataRepository()
                                          ->getPersonDataRepositories();
            if ($this->doesUserHaveRole(
                $userPerson,
                $personDataRepositories,
                array(DataRepositoryRoles::MANAGER)
            )) {
                return true;
            }
            // Research Group Person - Leadership, Admin, Data (aka DR-P/LAD)
            $personResearchGroups = $object->getPersonResearchGroups();
            // If user has one of ResearchGroupRole Leadership, Admin or Data they can edit the ResearchGroup object.
            $rgRoles = array(ResearchGroupRoles::LEADERSHIP, ResearchGroupRoles::ADMIN, ResearchGroupRoles::DATA);
            if ($this->doesUserHaveRole($userPerson, $personResearchGroups, $rgRoles)) {
                return true;
            }
        }
        return false;
    }
}
