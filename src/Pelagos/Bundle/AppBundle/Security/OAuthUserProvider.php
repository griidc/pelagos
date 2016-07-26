<?php

namespace Pelagos\Bundle\AppBundle\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as HwiOAuthUserProvider;

/**
 * Represents a class that loads UserInterface objects for a given oauth response.
 */
class OAuthUserProvider extends HwiOAuthUserProvider
{
    /**
     * Loads the user by a given UserResponseInterface object.
     *
     * @param UserResponseInterface $response A UserResponseInterface object.
     *
     * @return UserInterface
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $_SESSION['guestAuth'] = true;
        $_SESSION['guestAuthType'] = 'oauth2';
        $_SESSION['guestAuthProvider'] = $response->getResourceOwner()->getName();
        $email = $response->getEmail();
        $_SESSION['guestAuthUser'] = $email;
        return $this->loadUserByUsername($email);
    }
}
