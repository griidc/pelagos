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
    public function countDownloads(string $start = '', string $stop = ''): int
    {
        if ($start != '' and $stop != '') {
            $qb = $this->createQueryBuilder('log')
            ->select('log.creationTimeStamp, log.subjectEntityId')
            ->where('log.subjectEntityName = ?1')
            ->andWhere('log.actionName = ?2')
            ->andWhere('log.creationTimeStamp >= ?3')
            ->andWhere('log.creationTimeStamp <= ?4')
            ->orderBy('log.subjectEntityId', 'ASC')
            ->addOrderBy('log.creationTimeStamp', 'ASC')
            ->setParameter(1, 'Pelagos\Entity\Dataset')
            ->setParameter(2, 'File Download')
            ->setParameter(3, $start)
            ->setParameter(4, $stop);
        } else {
            $qb = $this->createQueryBuilder('log')
            ->select('log.creationTimeStamp, log.subjectEntityId')
            ->where('log.subjectEntityName = ?1')
            ->andWhere('log.actionName = ?2')
            ->orderBy('log.subjectEntityId', 'ASC')
            ->addOrderBy('log.creationTimeStamp', 'ASC')
            ->setParameter(1, 'Pelagos\Entity\Dataset')
            ->setParameter(2, 'File Download');
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
