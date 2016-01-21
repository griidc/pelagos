<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CreateVoter extends Voter
{
    
    protected function supports($attribute, $object)
    {
        if ($attribute != 'CAN_CREATE') {
            return false;
        }
    }
    
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        // TODO: Implement voteOnAttribute() method.
    }

}
