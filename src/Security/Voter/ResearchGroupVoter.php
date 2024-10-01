<?php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\DataRepository;
use App\Entity\DataRepositoryRole;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\ResearchGroup;
use App\Entity\ResearchGroupRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter to determine if a ResearchGroup can be created or edited.
 */
class ResearchGroupVoter extends PelagosEntityVoter
{
    public const CAN_CREATE_DIF_FOR = 'CAN_CREATE_DIF_FOR';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Make sure the object is an instance of ResearchGroup
        if (!$subject instanceof ResearchGroup) {
            return false;
        }

        // If the attribute isn't one of CAN_EDIT, CAN_DELETE, or CAN_CREATE_DIF_FOR, we cannot vote.
        if (
            !in_array($attribute, [
            PelagosEntityVoter::CAN_EDIT,
            PelagosEntityVoter::CAN_DELETE,
            self::CAN_CREATE_DIF_FOR])
        ) {
            return false;
        }

        // Only if the tree is as expected, vote.
        if (
            ($subject
                ->getFundingCycle()
                instanceof FundingCycle)
            and ($subject
                ->getFundingCycle()
                ->getFundingOrganization()
                instanceof FundingOrganization)
            and ($subject
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

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // Action: CAN_EDIT  Role: LEADERSHIP, ADMIN or DATA
        // If attribute is CAN_EDIT and user role is one of LEADERSHIP, ADMIN, DATA  n PersonResearchGroup
        // the user is authorized for the action.
        if (in_array($attribute, [PelagosEntityVoter::CAN_EDIT])) {
            // Research Group Person - Leadership, Admin, Data (aka DR-P/LAD)
            $personResearchGroups = $subject->getPersonResearchGroups();
            // If user has one of ResearchGroupRole Leadership, Admin or Data they can edit the ResearchGroup object.
            $rgRoles = [ResearchGroupRole::LEADERSHIP, ResearchGroupRole::ADMIN, ResearchGroupRole::DATA];
            if ($this->doesUserHaveRole($userPerson, $personResearchGroups, $rgRoles)) {
                return true;
            }
        }

        if (self::CAN_CREATE_DIF_FOR === $attribute) {
            $personDataRepositories = $userPerson->getPersonDataRepositories()->filter(
                function ($personDataRepository) use ($subject) {
                    return !$personDataRepository->isSameTypeAndId($subject);
                }
            );
            // Data Repository Managers can create DIFs for all Research Groups.
            if (
                $this->doesUserHaveRole(
                    $userPerson,
                    $personDataRepositories,
                    [DataRepositoryRole::MANAGER]
                )
            ) {
                return true;
            }

            // Regular people can create DIFs for Research Groups they are associated with.
            $personResearchGroups = $subject->getPersonResearchGroups();
            foreach ($personResearchGroups as $personResearchGroup) {
                if ($userPerson->isSameTypeAndId($personResearchGroup->getPerson())) {
                    return true;
                }
            }
        }

        // The default is to not authorize.
        return false;
    }
}
