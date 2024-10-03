<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Account;
use App\Entity\DIF;
use App\Entity\DataRepositoryRole;
use App\Security\EntityProperty;

/**
 * A voter to determine if a actions are possible by the user on a DIF subject.

 * @package Pelagos\Bundle\AppBundle\Security
 */
class DIFVoter extends PelagosEntityVoter
{
    /**
     * These attributes represent actions that the voter may be asked about.
     */
    const CAN_SUBMIT  = 'CAN_SUBMIT';
    const CAN_APPROVE = 'CAN_APPROVE';
    const CAN_REJECT  = 'CAN_REJECT';
    const CAN_UNLOCK  = 'CAN_UNLOCK';
    const CAN_REQUEST_UNLOCK  = 'CAN_REQUEST_UNLOCK';

    /**
     * Determine if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute denoting an action.
     * @param mixed  $subject    The subject of creation, deletion or change.
     */
    // Next line to be ignored because implemented function does not have type-hint on $attribute.
    // phpcs:ignore
    protected function supports(string $attribute, mixed $subject): bool
    {
        // If the subject is an EntityProperty.
        if ($subject instanceof EntityProperty) {
            // If the property is not 'status' we abstain.
            if ($subject->getProperty() != 'status') {
                return false;
            }
            // Make the Entity the subject for further inspection.
            $subject = $subject->getEntity();
        }

        // Make sure the subject is an instance of DIF
        if (!$subject instanceof DIF) {
            return false;
        }

        // Supports create, edit, submit, approve, reject, unlock, and request unlock.
        if (
            in_array(
                $attribute,
                array(
                self::CAN_CREATE,
                self::CAN_EDIT,
                self::CAN_SUBMIT,
                self::CAN_APPROVE,
                self::CAN_REJECT,
                self::CAN_UNLOCK,
                self::CAN_REQUEST_UNLOCK,
                )
            )
        ) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    /**
     * Perform a authorization test on an attribute, DIF subject and authentication token.
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     */
    // Next line to be ignored because implemented function does not have type-hint on $attribute.
    // phpcs:ignore
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        $userPerson = $user->getPerson();

        // If the subject is an EntityProperty
        if ($subject instanceof EntityProperty) {
            // If attribute is CAN_EDIT and the property is 'status'
            if (
                in_array($attribute, array(self::CAN_EDIT)) and
                $subject->getProperty() == 'status'
            ) {
                return true;
            }
            return false;
        }

        $personDataRepositories = $userPerson->getPersonDataRepositories()->filter(
            function ($personDataRepository) use ($subject) {
                return (!$personDataRepository->isSameTypeAndId($subject));
            }
        );
        // Data Repository Managers can submit, approve, reject, and unlock
        if (
            $this->doesUserHaveRole(
                $userPerson,
                $personDataRepositories,
                array(DataRepositoryRole::MANAGER)
            ) and in_array(
                $attribute,
                array(
                self::CAN_SUBMIT,
                self::CAN_APPROVE,
                self::CAN_REJECT,
                self::CAN_UNLOCK,
                )
            )
        ) {
            return true;
        }

        // If research group is locked, vote false.
        if (true === $subject->getResearchGroup()->isLocked()) {
            return false;
        }

        // Anyone can create, submit, or request unlock.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_SUBMIT, self::CAN_REQUEST_UNLOCK))) {
            return true;
        }

        // Anyone can update if not locked.
        if (self::CAN_EDIT === $attribute and !$subject->isLocked()) {
            return true;
        }

        return false;
    }
}
