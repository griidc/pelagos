<?php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\DataRepository;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Entity\ResearchGroupRole;
use App\Security\EntityProperty;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class PersonResearchGroupVoter Grant or deny authority to PersonResearchGroup objects.
 *
 * This is a Symfony voter that operates only on objects of type PersonResearchGroup.
 *
 * @see     supports() to see which operations attributes are supported
 */
class PersonResearchGroupVoter extends PelagosEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // If the object is an EntityProperty.
        if ($subject instanceof EntityProperty) {
            // If the property is not 'label' we abstain.
            if ('label' != $subject->getProperty()) {
                return false;
            }
            // Make the Entity the object for further inspection.
            $subject = $subject->getEntity();
        }
        if (!$subject instanceof PersonResearchGroup) {
            return false;
        }
        if (!in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE])) {
            return false;
        }
        // Only if the tree is as expected, vote.
        if (
            ($subject
                    ->getResearchGroup()
                instanceof ResearchGroup)
            and ($subject
                    ->getResearchGroup()
                    ->getFundingCycle()
                instanceof FundingCycle)
            and ($subject
                    ->getResearchGroup()
                    ->getFundingCycle()
                    ->getFundingOrganization()
                instanceof FundingOrganization)
            and ($subject
                    ->getResearchGroup()
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
        // If the user token does not contain an Account object return false.
        if (!$user instanceof Account) {
            return false;
        }

        //  Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // If the object is an EntityProperty
        if ($subject instanceof EntityProperty) {
            // If attribute is CAN_EDIT and the property is 'label' and
            // the Person associated with the Entity is the same as the authenticated user,
            // the user is authorized for the action.
            $entity = $subject->getEntity();
            if ($entity instanceof Account) {
                if (
                    in_array($attribute, [self::CAN_EDIT])
                    and 'label' == $subject->getProperty()
                    and $userPerson->isSameTypeAndId($entity->getPerson())
                ) {
                    return true;
                }
            }

            return false;
        }

        // Action: CAN_CREATE, CAN_EDIT, or CAN_DELETE
        // Role Type: Research Group
        // Roles: LEADERSHIP, ADMIN or DATA
        // If attribute is CAN_CREATE, CAN_EDIT, or CAN_DELETE and user role is one of
        // Research Group LEADERSHIP, ADMIN or DATA, the user is authorized for the action.
        if (in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE])) {
            // Get all PersonResearchGroups for the $subject's ResearchGroup but
            // exclude the one we are attempting to create.
            $authPersonResearchGroups = $subject->getResearchGroup()->getPersonResearchGroups()->filter(
                //  anonymous function to exclude the subject from the list of PersonResearchGroups
                function ($personResearchGroup) use ($subject) {
                    return $personResearchGroup !== $subject;
                }
                // end of anonymous function
            );
            // If the user has a PersonResearchGroup with a Role that is one of [Leadership, Admin or Data]
            // the user can create (persist) the subject PersonResearchGroup ($subject).
            $targetRoles = [ResearchGroupRole::LEADERSHIP, ResearchGroupRole::ADMIN, ResearchGroupRole::DATA];
            if ($this->doesUserHaveRole($userPerson, $authPersonResearchGroups, $targetRoles)) {
                return true;
            }
        }

        return false;
    }
}
