<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * A voter to determine if a ResearchGroup can be created.
 */
class CreateRGVoter extends Voter
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
        if ($attribute != 'CAN_CREATE') {
            return false;
        }

        if (!$object instanceof \Pelagos\Entity\ResearchGroup) {
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

        if (!$user instanceof \Pelagos\Entity\Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        $personDataRepositories = $object
            ->getFundingCycle()
            ->getFundingOrganization()
            ->getDataRepository()
            ->getPersonDataRepositories();

        foreach ($personDataRepositories as $personDR) {
            $roleName = $personDR->getRole()->getName();
            $person = $personDR->getPerson();

            if ($userPerson === $person and in_array($roleName, array('Manager'))) {
                return true;
            }
        }

        return false;
    }
}
