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
        
        return true;
    }
    
    protected function voteOnAttribute($attribute, $object, TokenInterface $token)
    {
        
        $user = $token->getUser();
        
        //$user->getAccount();
        return true;
    }

}
