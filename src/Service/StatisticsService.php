<?php
namespace App\Service;

use App\Entity\Dataset;
use App\Entity\LogActionItem;
use App\Util\FundingOrgFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

class StatisticsService
{
    public const NUMBEROFTOPDOWNLOADSTOSHOW = 10;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FundingOrgFilter $fundingOrgFilter,
    ) {
    }

    /**
     * Returns Year and Quarter given a timestamp.
     *
     * @return array{year:int|string, quarter:int} $yearQuarter
     */
    public function determineQuarter(\DateTime $timestamp): array
    {
        $year = $timestamp->format('Y');
        $month = $timestamp->format('n');
        $quarter = (int) ceil($month / 3);

        return ["year" => $year, "quarter" => $quarter];
    }

    /**
     * Maintains array of quarter counts.
     */
    public function quarterize(\DateTime $timestamp, array &$quarterCounts): void
    {
        $yearAndQuarter = $this->determineQuarter($timestamp);
        $year = $yearAndQuarter['year'];
        $quarter = $yearAndQuarter['quarter'];

        if (!array_key_exists($year, $quarterCounts)) {
            $quarterCounts[$year] = [];
            $quarterCounts[$year][0] = 0; // Q1
            $quarterCounts[$year][1] = 0;
            $quarterCounts[$year][2] = 0;
            $quarterCounts[$year][3] = 0;
        }

        $quarterCounts[$year][$quarter - 1] += 1;
    }

    /**
     * Returns array of UDIs of top downloads in date range.
     *
     * @param  int        $count    Number of top downloads to return
     * @param  int|null   $year     The year to get top downloads from
     * @param  int|null   $quarter  The quarter to get top downloads from
     * @return array<string,int>    Associative array of top UDIs with counts
     *
     * @throws \Exception If quarter value used other than (1, 2, 3, 4)
     */
    public function getTopDatasetsDownloadedByYearAndQuarter(int $count, int $year = null, int $quarter = null): array
    {
        if ($quarter !== null && !(in_array($quarter, [1, 2, 3, 4], true))) {
            throw new \Exception("Bad quarter specified, use 1-4.");
        }

        if ($year === null && $quarter !== null) {
            throw new \Exception("If quarter is specified, year must be too.");
        }

        if ($year !== null && $quarter == 1) {
            $from = "$year-01-01";
            $to = "$year-03-31";
        } elseif ($year !== null && $quarter == 2) {
            $from = "$year-04-01";
            $to = "$year-06-30";
        } elseif ($year !== null && $quarter == 3) {
            $from = "$year-07-01";
            $to = "$year-09-30";
        } elseif ($year !== null && $quarter == 4) {
            $from = "$year-10-01";
            $to = "$year-12-31";
        } elseif ($year !== null && $quarter === null) {
            $from = "$year-01-01";
            $to = "$year-12-31";
        }

        $qb = $this->entityManager->getRepository(LogActionItem::class)->createQueryBuilder('log')
            ->select('count(log.subjectEntityId), log.subjectEntityId')
            ->where('log.subjectEntityName = :entityName')
            ->andWhere('log.actionName = :actionName')
            ->orderBy('count(log.subjectEntityId)', 'DESC')
            ->groupBy('log.subjectEntityId')
            ->setMaxResults($count)
            ->setParameter('entityName', 'Pelagos\\Entity\\Dataset')
            ->setParameter('actionName', 'File Download');

        if ($year !== null) {
            $qb
                ->andWhere('log.creationTimeStamp >= :from')
                ->andWhere('log.creationTimeStamp <= :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
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
        $results = $query->getResult();

        $topDownloadUdis = [];
        foreach ($results as $row) {
            $countVal = $row[1];
            $id = $row['subjectEntityId'];
            $dataset = $this->entityManager->find(Dataset::class, $id);
            if ($dataset instanceof Dataset) {
                $udi = $dataset->getUdi();
                $topDownloadUdis[$udi] = $countVal;
            }
        }

        return $topDownloadUdis;
    }

    /**
     * Generates array of funding-org filter-aware download events.
     *
     * @return array<int, array{0:int,1:\DateTime,2:string}>
     */
    public function getDownloads(): array
    {
        $qb = $this->entityManager->getRepository(LogActionItem::class)->createQueryBuilder('log')
            ->select('log.creationTimeStamp, log.subjectEntityId, log.payLoad')
            ->where('log.subjectEntityName = :entityName')
            ->andWhere('log.actionName = :actionName')
            ->orderBy('log.subjectEntityId', 'ASC')
            ->addOrderBy('log.creationTimeStamp', 'ASC')
            ->setParameter('entityName', 'Pelagos\\Entity\\Dataset')
            ->setParameter('actionName', 'File Download');

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

        $currentTimeStamp = 0;
        $downloadArray = [];
        $currentId = 0;
        foreach ($downloads as $key => $timeStamp) {
            $id = $timeStamp['subjectEntityId'];
            $dateTime = $timeStamp['creationTimeStamp'];
            $epochTime = (int) $dateTime->format('U');
            $displayTime = $dateTime->format('Y-m-d');

            if (($displayTime === '2014-09-27') || $key === array_key_first($downloads) || ($epochTime - $currentTimeStamp) > 30 || $currentId != $id) {
                $currentTimeStamp = $epochTime;

                $user = (($timeStamp['payLoad']['userId'] ?? 'anonymous') === 'anonymous') ? 'anonymous' : 'logged-in';

                $downloadArray[] = [$id, $dateTime, $user];
            }

            $currentId = $id;
        }

        return $downloadArray;
    }
}
