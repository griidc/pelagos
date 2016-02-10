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
     * Perform a single authorization test on an attribute, PersonResearchGroup subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action for which the user seeks authorization.
     * @param mixed          $object    A PersonResearchGroup to be created (persisted).
     * @param TokenInterface $token     A security token containing user authentication information.
     * @see this->supports($attribute, $object)
     *
     * @return boolean True if the attribute is allowed on the subject for the user specified by the token.
     */
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        //  we know the attribute is "CAN_CREATE" and the object is a PersonResearchGroup
        $user = $token->getUser();
        // If the user token does not contain an Account object return false.
        if (!$user instanceof Account) {
            return false;
        }

        //  Get the Person associated with this Account.
        $userPerson = $user->getPerson();

        // if the user (Person) has a PersonResearchGroup with a Role name that is one of [Leadership, Admin or Data]
        // and that PersonResearchGroup's ResearchGroup matches the ResearchGroup of the $object (voter subject)
        // the user can create (persist) the subject PersonResearchGroup ($object).
        $targetRoles = array(ResearchGroupRoles::LEADERSHIP, ResearchGroupRoles::ADMIN, ResearchGroupRoles::DATA);
        $objectResearchGroup = $object->getResearchGroup();
        $userPersonResearchGroups = $userPerson->getPersonResearchGroups();
        foreach($userPersonResearchGroups as $userPersonResearchGroup) {
            $currentResearchGroup = $userPersonResearchGroup->getResearchGroup();
            $currentRoleName = $userPersonResearchGroup->getRole()->getName();
            if (in_array($currentRoleName, $targetRoles) &&
                $currentResearchGroup->isSameTypeAndId($objectResearchGroup)) {
                return true;
            }
        }
        return false;
    }
}