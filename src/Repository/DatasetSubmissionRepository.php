<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Util\FundingOrgFilter;

/**
 * DatasetSubmission Entity Repository class.
 */
class DatasetSubmissionRepository extends ServiceEntityRepository
{
    /**
     * Utility to filter by funding organization.
     *
     * @var FundingOrgFilter
     */
    private $fundingOrgFilter;

    /**
     * Constructor.
     *
     * @param ManagerRegistry  $registry         The Registry Manager.
     * @param FundingOrgFilter $fundingOrgFilter Utility to filter by funding organization.
     */
    public function __construct(ManagerRegistry $registry, FundingOrgFilter $fundingOrgFilter)
    {
        parent::__construct($registry, DatasetSubmission::class);

        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Get Registered Dataset Submissions.
     *
     * @return array
     */
    public function getRegisteredDatasets()
    {
        $queryBuilder = $this->createQueryBuilder('datasetSubmission');

        $queryBuilder
            ->select('datasetSubmission.creationTimeStamp')
            ->where('datasetSubmission.id IN (
                        SELECT MIN(subDatasetSubmission.id)
                        FROM ' . DatasetSubmission::class . ' subDatasetSubmission
                        GROUP BY subDatasetSubmission.dataset
                    )')
            ->orderBy('datasetSubmission.creationTimeStamp');

        if ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $queryBuilder
            ->innerJoin('datasetSubmission.dataset', 'ds')
            ->innerJoin('ds.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        return $queryBuilder->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Get Available Dataset Submissions.
     *
     * @return array
     */
    public function getAvailableDatasets()
    {
        $queryBuilder = $this->createQueryBuilder('datasetSubmission');

        $queryBuilder
            ->select('datasetSubmission.creationTimeStamp')
            ->where('datasetSubmission.id IN (
                SELECT MIN(subDatasetSubmission.id)
                FROM ' . DatasetSubmission::class . ' subDatasetSubmission
                WHERE subDatasetSubmission.datasetStatus = :metadatastatus
                AND subDatasetSubmission.restrictions = :restrictedstatus
                AND subDatasetSubmission.datasetFileTransferStatus = :transerstatuscompleted
                GROUP BY subDatasetSubmission.dataset
            )')
            ->setParameters(
                new ArrayCollection(array(
                    new Parameter('metadatastatus', Dataset::DATASET_STATUS_ACCEPTED),
                    new Parameter('restrictedstatus', DatasetSubmission::RESTRICTION_NONE),
                    new Parameter('transerstatuscompleted', DatasetSubmission::TRANSFER_STATUS_COMPLETED),
                ))
            )
            ->orderBy('datasetSubmission.creationTimeStamp');

        if ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $queryBuilder
            ->innerJoin('datasetSubmission.dataset', 'ds')
            ->innerJoin('ds.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        return $queryBuilder->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}
