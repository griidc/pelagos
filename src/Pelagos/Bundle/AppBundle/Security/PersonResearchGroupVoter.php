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
 *
 * @see     supports() to see which operations attributes are supported
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
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();
        // If the user token does not contain an Account object return false.
        if (!$user instanceof Account) {
            return false;
        }

        //  Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // if attribute is CAN_CREATE check to see if this user has a role with sufficient authority.
        if ($attribute == self::CAN_CREATE) {
            // if the user has a PersonResearchGroup with a Role name that is one of [Leadership, Admin or Data]
            // and that PersonResearchGroup's ResearchGroup matches the ResearchGroup of the $object (voter subject)
            // the user can create (persist) the subject PersonResearchGroup ($object).
            $targetRoles = array(ResearchGroupRoles::LEADERSHIP, ResearchGroupRoles::ADMIN, ResearchGroupRoles::DATA);
            //  The ResearchGroup instance to which the subject refers.
            $objectResearchGroup = $object->getResearchGroup();
            //  all of this User's  PersonResearchGroup relationships
            $userPersonResearchGroups = $userPerson->getPersonResearchGroups();
            foreach ($userPersonResearchGroups as $currentPersonResearchGroup) {
                //  don't use the subject PersonResearchGroup $object as authorization to store the $object - ignore it
                if ($currentPersonResearchGroup !== $object) {
                    //   the ResearchGroup considered in this pass through the loop
                    $currentResearchGroup = $currentPersonResearchGroup->getResearchGroup();
                    //  Only consider ResearchGroups that are the same as the subject's ResearchGroup
                    if ($currentResearchGroup->isSameTypeAndId($objectResearchGroup)) {
                        //  the role name of the current PersonResearchGroup
                        $currentRoleName = $currentPersonResearchGroup->getRole()->getName();
                        // If the current PersonResearchGroup Role name is one of those in the $targetRoles return true.
                        if (in_array($currentRoleName, $targetRoles)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
}
