<?php
namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Pelagos\Entity\Account;
use Pelagos\Entity\DIF;

/**
 * A voter to determine if a actions are possible by the user on a DIF object.

 * @package Pelagos\Bundle\AppBundle\Security
 */
class DIFVoter extends PelagosEntityVoter
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
        // Make sure the object is an instance of DIF
        if (!$object instanceof DIF) {
            return false;
        }

        // Otherwise vote.
        return true;
    }

    /**
     * Perform a authorization test on an attribute, DIF subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $object    A DIF.
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

        // Eventually this will actually check something.
        return true;
    }
}
