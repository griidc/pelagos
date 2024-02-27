<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use App\Entity\Dataset;
use App\Entity\LogActionItem;
use App\Util\FundingOrgFilter;

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
     * Count of downloads, optional date range and also filter aware.
     *
     * @return integer The count of the datasets downloaded, per FAIR guidelines.
     */
    public function countDownloads(\DateTime $start = null, \DateTime $stop = null): int
    {
        $qb = $this->createQueryBuilder('log')
        ->select('log.creationTimeStamp, log.subjectEntityId')
        ->where('log.subjectEntityName = :entityName')
        ->andWhere('log.actionName = :actionName')
        ->orderBy('log.subjectEntityId', 'ASC')
        ->addOrderBy('log.creationTimeStamp', 'ASC')
        ->setParameter('entityName', 'Pelagos\Entity\Dataset')
        ->setParameter('actionName', 'File Download');

        if ($start instanceof \DateTime and $stop instanceof \DateTime) {
            $dbFormatStartTime = $start->format('Y-m-d H:i:sO');
            $dbFormatEndTime = $stop->format('Y-m-d H:i:sO');

            $qb
            ->andWhere('log.creationTimeStamp >= :start')
            ->andWhere('log.creationTimeStamp <= :stop')
            ->setParameter('start', $dbFormatStartTime)
            ->setParameter('stop', $dbFormatEndTime);
        }

        if ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $qb
            ->join(Dataset::class, 'dataset', Query\Expr\Join::WITH, 'log.subjectEntityId = dataset.id')
            ->innerJoin('dataset.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        $query = $qb->getQuery();
        $downloads = $query->getResult();

        // Setup variable to exist.
        $currentTimeStamp = 0;
        $downloadCount = 0;
        $currentId = 0;
        foreach ($downloads as $key => $timeStamp) {
            $id = $timeStamp['subjectEntityId'];
            $dateTime = $timeStamp['creationTimeStamp'];
            $epochTime = (int) $dateTime->format('U');

            if ($key === array_key_first($downloads) or ($epochTime - $currentTimeStamp) > 30 or $currentId <> $id) {
                $currentTimeStamp = $epochTime;
                $downloadCount++;
            }

            $currentId = $id;
        }

        return $downloadCount;
    }
}
