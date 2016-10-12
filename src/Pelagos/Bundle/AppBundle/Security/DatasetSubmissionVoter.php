<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;

/**
 * A voter to determine if a actions are possible by the user on a Dataset Submission.
 *
 * @package Pelagos\Bundle\AppBundle\Security
 */
class DatasetSubmissionVoter extends PelagosEntityVoter
{
    /**
     * Determine if an attribute and subject are supported by this voter.
     *
     * @param string $attribute The action to be considered.
     * @param mixed  $subject   The subject the action would be performed on.
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $subject)
    {
        // Abstain if the subject is not an instance of DatasetSubmission.
        if (!$subject instanceof DatasetSubmission) {
            return false;
        }

        // Supports create and edit.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT))) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    /**
     * Test authorization of the action specified by $attribute on $subject for the current user.
     *
     * This is only called if $this->supports($attribute, $subject) returns true for the same $attribute and $subject.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $subject   The subject the action would be performed on.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True if the current user is authorized to perform the action specified by $attribute on $subject.
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Anyone with an account can edit.
        if (self::CAN_EDIT == $attribute) {
            return true;
        }

        // A user with an account can only create dataset submissions
        // associated with research groups that they (the user) are a member of.
        $researchGroups = $user->getPerson()->getResearchGroups();
        $submissionResearchGroup = $subject->getDataset()->getResearchGroup();

        if (self::CAN_CREATE == $attribute) {
            if (null !== $submissionResearchGroup and in_array($submissionResearchGroup, $researchGroups)) {
                return true;
            }
        }

        return false;
    }
}
