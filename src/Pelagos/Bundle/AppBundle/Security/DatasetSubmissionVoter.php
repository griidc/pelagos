<?php

namespace Pelagos\Bundle\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmission;
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
        if (!$subject instanceof DatasetSubmission and !$subject instanceof PersonDatasetSubmission) {
            return false;
        }

        // Supports create and edit.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE))) {
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

        // A user with an account can only create or edit dataset submissions
        // associated with research groups that they (the user) are a member of.

        $researchGroups = $user->getPerson()->getResearchGroups();
        if ($subject instanceof DatasetSubmission) {
            $submissionResearchGroup = $subject->getDataset()->getResearchGroup();
            $submissionStatus = $subject->getStatus();
        } elseif ($subject instanceof PersonDatasetSubmission) {
            $submissionResearchGroup = $subject->getDatasetSubmission()->getDataset()->getResearchGroup();
            $submissionStatus = $subject->getDatasetSubmission()->getStatus();
        } else {
            return false;
        }

        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT))) {
            if (null !== $submissionResearchGroup) {
                foreach ($researchGroups as $researchGroup) {
                    if ($submissionResearchGroup->isSameTypeAndId($researchGroup)) {
                        return true;
                    }
                }
            }
        } elseif (self::CAN_DELETE === $attribute and DatasetSubmission::STATUS_INCOMPLETE === $submissionStatus) {
            if (null !== $submissionResearchGroup) {
                foreach ($researchGroups as $researchGroup) {
                    if ($submissionResearchGroup->isSameTypeAndId($researchGroup)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
