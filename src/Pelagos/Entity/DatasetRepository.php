<?php

namespace Pelagos\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Dataset Entity Repository class.
 */
class DatasetRepository extends EntityRepository
{
    /**
     * Count the number of registered Datasets.
     *
     * @return integer
     */
    public function countRegistered()
    {
        return $this->createQueryBuilder('dataset')
            ->select('COUNT(dataset)')
            ->where('dataset.datasetSubmissionStatus = :datasetSubmissionStatus')
            ->setParameter('datasetSubmissionStatus', DatasetSubmission::STATUS_COMPLETE)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
