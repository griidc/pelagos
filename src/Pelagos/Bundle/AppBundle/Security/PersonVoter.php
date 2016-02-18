<?php


namespace Pelagos\Bundle\AppBundle\Security;

use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pelagos\Entity\Account;
use Pelagos\Entity\Person;

/**
 * A voter to determine if a actions are possible by the user on a Person object.

 * @package Pelagos\Bundle\AppBundle\Security
 */
class PersonVoter extends PelagosEntityVoter
{
    /**
     * Determine if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute denoting an action.
     * @param mixed  $object    The subject of creation, deletion or change.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $object)
    {
        // Make sure the object is an instance of Person
        if (!$object instanceof Person) {
            return false;
        }

        // Supports PelagosEntityVoter::CAN_EDIT
        if (in_array($attribute, array(PelagosEntityVoter::CAN_EDIT))) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    /**
     * Perform a authorization test on an attribute, Person subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $object    A Person.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True If the user has one of the target roles for any of the subject's DataRepositories.
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

        // Action: PelagosEntityVoter::CAN_EDIT
        // Role:   DataRepositoryRoles::MANAGER
        // If attribute is CAN_EDIT and user has the role MANAGER for a DataRepository that [the user
        // and the subject Person] have in common via association, then the user can edit the object.
        if ($attribute == PelagosEntityVoter::CAN_EDIT) {
            // The DataRepositoryRoles that we are looking for.
            $voterRoles = array(DataRepositoryRoles::MANAGER);
            // get all of the PersonDataRepositories for the user that have the one of the target Roles
            // and the subject Person ($object) is not the User
            $userPersonDataRepositoriesWithAuthority = $userPerson->getPersonDataRepositories()->filter(
                // Exclude the subject from the list of PersonResearchGroups
                // and include only those with one of the target Roles
                function ($itemInCollection) use ($object, $voterRoles) {
                    return (!$itemInCollection->getPerson()->isSameTypeAndId($object) &&
                        in_array($itemInCollection->getRole()->getName(), $voterRoles));
                }
                // end of anonymous function
            );

            //  if there are no user Roles that match one of the target Roles the user does not have authority.
            if ($userPersonDataRepositoriesWithAuthority->isEmpty()) {
                return false;
            }
            // get all the DataRepositories with witch the subject is associated..
            $objectDataRepositories = $object->getDataRepositories();

            // if the user has one of the target Roles on a DataRepository that is also in the
            // DataRepository list of the subject Person, then the user is granted Authority

            foreach ($userPersonDataRepositoriesWithAuthority as $userPdrWithAuthority) {
                if ($objectDataRepositories->contains($userPdrWithAuthority->getDataRepository())) {
                    return true;
                }
            }
        }
        return false;
    }
}
