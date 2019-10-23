<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\DataRepository;
use App\Entity\Account;
use App\Entity\ResearchGroupRole;

use App\Security\EntityProperty;

/**
 * Class PersonResearchGroupVoter Grant or deny authority to PersonResearchGroup objects.
 *
 * This is a Symfony voter that operates only on objects of type PersonResearchGroup.
 *
 * @see     supports() to see which operations attributes are supported
 * @package App\Security\Voter
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
    protected function supports($attribute, $object) //phpcs:ignore
    {
        // If the object is an EntityProperty.
        if ($object instanceof EntityProperty) {
            // If the property is not 'label' we abstain.
            if ($object->getProperty() != 'label') {
                return false;
            }
            // Make the Entity the object for further inspection.
            $object = $object->getEntity();
        }
        if (!$object instanceof PersonResearchGroup) {
            return false;
        }
        if (!in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE))) {
            return false;
        }
        // Only if the tree is as expected, vote.
        if (($object
                ->getResearchGroup()
                instanceof ResearchGroup) and
            ($object
                ->getResearchGroup()
                ->getFundingCycle()
                instanceof FundingCycle) and
            ($object
                ->getResearchGroup()
                ->getFundingCycle()
                ->getFundingOrganization()
                instanceof FundingOrganization) and
            ($object
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

    /**
     * Perform a single authorization test on an attribute, PersonResearchGroup subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action for which the user seeks authorization.
     * @param mixed          $object    The subject of the voter, A PersonResearchGroup to be created (persisted).
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @see this->supports($attribute, $object)
     *
     * @return boolean True if the attribute is allowed on the subject for the user specified by the token.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token) //phpcs:ignore
    {
        $user = $token->getUser();
        // If the user token does not contain an Account object return false.
        if (!$user instanceof Account) {
            return false;
        }

        //  Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // If the object is an EntityProperty
        if ($object instanceof EntityProperty) {
            // If attribute is CAN_EDIT and the property is 'label' and
            // the Person associated with the Entity is the same as the authenticated user,
            // the user is authorized for the action.
            if (in_array($attribute, array(self::CAN_EDIT)) and
                $object->getProperty() == 'label' and
                $userPerson->isSameTypeAndId($object->getEntity()->getPerson())) {
                return true;
            }
            return false;
        }

        // Action: CAN_CREATE, CAN_EDIT, or CAN_DELETE
        // Role Type: Research Group
        // Roles: LEADERSHIP, ADMIN or DATA
        // If attribute is CAN_CREATE, CAN_EDIT, or CAN_DELETE and user role is one of
        // Research Group LEADERSHIP, ADMIN or DATA, the user is authorized for the action.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE))) {
            // Get all PersonResearchGroups for the $object's ResearchGroup but
            // exclude the one we are attempting to create.
            $authPersonResearchGroups = $object->getResearchGroup()->getPersonResearchGroups()->filter(
                //  anonymous function to exclude the subject from the list of PersonResearchGroups
                function ($personResearchGroup) use ($object) {
                    return ($personResearchGroup !== $object);
                }
                // end of anonymous function
            );
            // If the user has a PersonResearchGroup with a Role that is one of [Leadership, Admin or Data]
            // the user can create (persist) the subject PersonResearchGroup ($object).
            $targetRoles = array(ResearchGroupRole::LEADERSHIP, ResearchGroupRole::ADMIN, ResearchGroupRole::DATA);
            if ($this->doesUserHaveRole($userPerson, $authPersonResearchGroups, $targetRoles)) {
                return true;
            }
        }
        return false;
    }
}
