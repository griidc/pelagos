<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use App\Entity\Account;
use App\Entity\File as PelagosFile;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use Doctrine\ORM\EntityManager;

/**
 * A voter to determine if CRUD actions possible on a File.

 * @package App\Security\Voter
 */
class FileVoter extends PelagosEntityVoter
{
    /**
     * Class constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Entity manager for QB.
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Determine if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute denoting an action.
     * @param mixed  $subject   The subject of creation,
     *
     * @return boolean True if the attribute and subject are supported, false otherwise.
     */
    protected function supports($attribute, $subject) //phpcs:ignore
    {
        // Make sure the subject is an instance of File
        if (!$subject instanceof PelagosFile) {
            return false;
        }

        // Supports CUD.
        if (in_array($attribute, array(self::CAN_CREATE, self::CAN_EDIT, self::CAN_DELETE))) {
            return true;
        }

        // Otherwise abstain.
        return false;
    }

    /**
     * Perform a authorization test on an attribute, File subject and authentication token.
     *
     * The Symfony calling security framework calls supports before calling voteOnAttribute.
     *
     * @param string         $attribute The action to be considered.
     * @param mixed          $subject   A File.
     * @param TokenInterface $token     A security token containing user authentication information.
     *
     * @return boolean True If the user has one of the target roles for any of the subject's targets.
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) //phpcs:ignore
    {
        $user = $token->getUser();

        // If the user token does not contain an Account, vote false.
        if (!$user instanceof Account) {
            return false;
        }

        // Return TRUE if Account is of a user who's a member of the same RG as the Dataset.
        $person = $user->getPerson();
        $personsResearchGroups = $person->getResearchGroups();
        $datasetsResearchGroup = $dataset->getResearchGroup();
        $fileset = $subject->getFileset();

        // Find the linked dataset submission. Vote based on it's dataset's RG.
        $qb = $em->createQueryBuilder();

        $query = $qb
            ->SELECT('datasetsubmission', 'ds')
            ->FROM('\App\Entity\DatasetSubmission')
            ->WHERE(
                $qb->expr()->eq('ds.fileset', '?1')
            )
            ->setParameter(1, $fileset)
            ->getQuery();

        $datasetSubmission = $query->getSingleResult();

        if (!$datasetSubmission instanceof DatasetSubmission) {
            return false;
        }
        $dataset = $datasetSubmission->getDataset();

        if ($person instanceof Person and in_array($datasetsResearchGroup, $personsResearchGroups)) {
            return true;
        }

        return false;
    }
}
