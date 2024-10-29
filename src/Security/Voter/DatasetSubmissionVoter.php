<?php

namespace App\Security\Voter;

use App\Entity\Account;
use App\Entity\DatasetSubmission;
use App\Entity\DistributionPoint;
use App\Entity\File;
use App\Entity\PersonDatasetSubmission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * A voter to determine if a actions are possible by the user on a Dataset Submission.
 */
class DatasetSubmissionVoter extends PelagosEntityVoter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Abstain if the subject is not an instance of DatasetSubmission.
        if (
            !$subject instanceof DatasetSubmission and !$subject instanceof PersonDatasetSubmission
            and !$subject instanceof DistributionPoint and !$subject instanceof File
        ) {
            return false;
        }

        // Supports create and edit.
        if (in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE])) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // If research group is locked, vote false.
        if ($subject instanceof DatasetSubmission and true === $subject->getDataset()->getResearchGroup()->isLocked()) {
            return false;
        }

        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Granting permission to DatasetSubmission entity to create/edit Distribution point entity
        if ($subject instanceof DistributionPoint) {
            return true;
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
        } elseif ($subject instanceof File) {
            $submissionResearchGroup = $subject->getFileset()->getDatasetSubmission()->getDataset()->getResearchGroup();
            $submissionStatus = $subject->getFileset()->getDatasetSubmission()->getStatus();
        } else {
            return false;
        }

        if (in_array($attribute, [self::CAN_CREATE, self::CAN_EDIT])) {
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
