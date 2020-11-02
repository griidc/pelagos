<?php

namespace App\Repository;

use App\Entity\Dataset;
use App\Entity\LogActionItem;
use App\Util\FundingOrgFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LogActionItem Entity Repository class.
 */
class LogActionItemRepository extends ServiceEntityRepository
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
        parent::__construct($registry, LogActionItem::class);

        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Sum of all dataset file sizes.
     *
     * @return integer Size of data in bytes.
     */
    public function countDownloads(): int
    {
        $qb = $this->createQueryBuilder('log')
            ->select('COUNT(log.id)')
            ->where('log.subjectEntityName = ?1')
            ->andWhere('log.actionName = ?2')
            ->setParameter(1, 'Pelagos\Entity\Dataset')
            ->setParameter(2, 'File Download');

        if ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $qb
            ->join(Dataset::class, 'dataset', Query\Expr\Join::WITH, 'log.subjectEntityId = dataset.id')
            ->innerJoin('dataset.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }
}
