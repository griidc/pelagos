<?php


namespace Pelagos\Bundle\AppBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class JvhVoter Short name goes here. (Includes - GRIIDC PHP CLASS DOC).
 *
 * A longer more detailed description that can span
 * multiple lines goes here.
 * @package Pelagos\Bundle\AppBundle\Security
 */
class JvhVoter extends Voter
{
    const JVH_ACTION = 'JVH';

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
        if ($attribute != self::JVH_ACTION) {
            return false;
        }

        if (!$object instanceof FundingOrganization) {
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
            return false;
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