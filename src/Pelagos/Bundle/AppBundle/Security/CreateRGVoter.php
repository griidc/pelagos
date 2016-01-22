<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CreateRGVoter extends Voter
{

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

    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof \Pelagos\Entity\Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        $PersonDataRepositories = $object
                                      ->getFundingCycle()
                                      ->getFundingOrganization()
                                      ->getDataRepository()
                                      ->getPersonDataRepositories();

        foreach ($PersonDataRepositories as $personDR) {
            $roleName = $personDR->getRole()->getName();
            $person = $personDR->getPerson();

            if ($userPerson === $person and in_array($roleName, array('Developer'))) {
                return true;
            }
        }

        return false;
    }
}
