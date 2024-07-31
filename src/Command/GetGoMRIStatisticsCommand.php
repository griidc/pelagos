<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Entity\LogActionItem;
use App\Repository\LogActionItemRepository;
use App\Util\FundingOrgFilter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetGoMRIStatisticsCommand extends Command
{
    protected static $defaultName = 'pelagos:get-gomri-statistics';
    protected static $defaultDescription = 'Produce GoMRI report artifacts.';

    // These values were derived from studying the sequence of downloads
    // and determing the range of dates where the harvesting seemed to have
    // occurred.
    public const HARVEST2019COUNT = 7100; // count
    public const HARVEST2019DATA = 3600; // GB

    public const NUMBEROFTOPDOWNLOADSTOSHOW = 10;

    /**
     * Class constructor for dependency injection.
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LogActionItemRepository $logActionItemRepository,
        private FundingOrgFilter $fundingOrgFilter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();

        $datasetCount = 0;
        $gomriDatasetCount = 0;
        $gomriDatasetColdStorageCount = 0;
        $gomriDatasetTotalSize = 0;
        $gomriColdStorageDatasetTotalSize = 0;
        $totalGomriDatasetSubmittedSince2021Count = 0;
        $totalDatasetSubmittedSince2021Count = 0;
        $totalPostGomriDatasetsSubmittedByQuarter = [];
        $skipCount = 0;

        foreach ($datasets as $dataset) {
            if (DIF::STATUS_UNSUBMITTED === $dataset->getDif()->getStatus()) {
                continue;
            }
            ++$datasetCount;
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ('GoMRI' === $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getShortName()) {
                ++$gomriDatasetCount;
                if ($datasetSubmission instanceof DatasetSubmission) {
                    $this->quarterize($datasetSubmission->getSubmissionTimeStamp(), $totalPostGomriDatasetsSubmittedByQuarter);

                    if ($datasetSubmission->getSubmissionTimeStamp()->format('U') >= \DateTime::createFromFormat('d/m/Y', '01/01/2021', new \DateTimeZone('America/Chicago'))->format('U')) {
                        ++$totalGomriDatasetSubmittedSince2021Count;
                    }

                    if ($datasetSubmission->isDatasetFileInColdStorage()) {
                        ++$gomriDatasetColdStorageCount;
                        $gomriColdStorageDatasetTotalSize += $datasetSubmission->getColdStorageTotalUnpackedSize() ?? 0;
                    } else {
                        $gomriDatasetTotalSize += $datasetSubmission->getFileset()?->getFileSize() ?? 0;
                    }
                }
            }

            if ($datasetSubmission instanceof DatasetSubmission) {
                if ($datasetSubmission->getSubmissionTimeStamp()->format('U') >= \DateTime::createFromFormat('d/m/Y', '01/01/2021', new \DateTimeZone('America/Chicago'))->format('U')) {
                    ++$totalDatasetSubmittedSince2021Count;
                }
            }
        }

        $io->writeln("Total number of GoMRI Datasets: $gomriDatasetCount");
        $io->writeln('Total Size of GoMRI Datasets: ' . round(($gomriDatasetTotalSize + $gomriColdStorageDatasetTotalSize) / 1000000000000, 1) . ' TB');
        $io->writeln("Number of GoMRI Datasets in cold storage: $gomriDatasetColdStorageCount");
        $io->writeln('Total size of GoMRI Datasets in cold storage: ' . round($gomriColdStorageDatasetTotalSize / 1000000000000, 1) . ' TB');
        $io->writeln('Total number of GoMRI datasets submitted since 2021-01-01 until current date ' . $totalGomriDatasetSubmittedSince2021Count);
        $io->writeln('Total number of datasets (all data including GoMRI) submitted since 2021-01-01 until current date ' . $totalDatasetSubmittedSince2021Count);

        // GoMRI submissions by quarter:
        $years = array_keys($totalPostGomriDatasetsSubmittedByQuarter);
        sort($years);
        $minYear = $years[0];
        $maxYear = $years[sizeof($years) - 1];
        $io->writeln("\nGomri Uploads (submissions)\n");
        for ($i = $minYear; $i <= $maxYear; $i++) {
            if (in_array($i, $years)) {
                $io->writeln("Number of GoMRI datasets submitted $i"
                . ' Q1:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][0]
                . ' Q2:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][1]
                . ' Q3:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][2]
                . ' Q4:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][3]);
            }
        }

        $io->writeln("\nGoMRI Downloads:\n");
        $downloadSizeByYearAndQuarter = [];
        $downloadCountByYearAndQuarter = [];
        foreach ($this->getDownloads() as $datasetDownload) {
            $id = $datasetDownload[0];
            $timestamp = $datasetDownload[1];

            $yearQuarter = $this->determineQuarter($timestamp);
            $year = $yearQuarter['year'];
            $quarter = $yearQuarter['quarter'];

            if (!array_key_exists($year, $downloadCountByYearAndQuarter)) {
                $downloadCountByYearAndQuarter[$year][0] = 0;
                $downloadCountByYearAndQuarter[$year][1] = 0;
                $downloadCountByYearAndQuarter[$year][2] = 0;
                $downloadCountByYearAndQuarter[$year][3] = 0;
                $downloadCountByYearAndQuarter[$year][4] = 0;
            }

            if (!array_key_exists($year, $downloadSizeByYearAndQuarter)) {
                $downloadSizeByYearAndQuarter[$year][0] = 0;
                $downloadSizeByYearAndQuarter[$year][1] = 0;
                $downloadSizeByYearAndQuarter[$year][2] = 0;
                $downloadSizeByYearAndQuarter[$year][3] = 0;
                $downloadSizeByYearAndQuarter[$year][4] = 0;
            }

            $dataset = $this->entityManager->find('\App\Entity\Dataset', $id);
            // We may also need to consider using the right size for the external-logged download requests
            // that also get counted here, but end up getting the stub sizes. There aren't many though.
            if ($dataset instanceof Dataset) {
                $size = $dataset->getTotalFileSize();
            } else {
                $size = 0;
                $skipCount++;
            }

            $downloadCountByYearAndQuarter[$year][$quarter]++;
            $downloadSizeByYearAndQuarter[$year][$quarter] += $size / 1000000000;
        }

        // array_keys still onlys dump first level, years, for $array[year][quarter].
        $firstYear = min(array_keys($downloadCountByYearAndQuarter));
        $lastYear = max(array_keys($downloadCountByYearAndQuarter));

        // Deal with the data harvest of 2019Q2.
        $downloadCountByYearAndQuarter[2019][2] -= self::HARVEST2019COUNT;
        $downloadSizeByYearAndQuarter[2019][2] -= self::HARVEST2019DATA;

        $totalDownloads = 0;
        $totalDownloadSize = 0;
        for ($year = $firstYear; $year <= $lastYear; $year++) {
            $yearCountTotal = 0;
            $yearSizeTotal = 0;
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $popularDownloads = $this->getTopDatasetsDownloadedByYearAndQuarter(self::NUMBEROFTOPDOWNLOADSTOSHOW, $year, $quarter);
                $io->writeln($year . '/Q' . $quarter . ' Download Count: ' . $downloadCountByYearAndQuarter[$year][$quarter]
                . ', ' . 'Total Size (GB): ' . round($downloadSizeByYearAndQuarter[$year][$quarter]));
                $yearCountTotal += $downloadCountByYearAndQuarter[$year][$quarter];
                $totalDownloads += $downloadCountByYearAndQuarter[$year][$quarter];
                $yearSizeTotal += $downloadSizeByYearAndQuarter[$year][$quarter];
                $totalDownloadSize += $downloadSizeByYearAndQuarter[$year][$quarter];
                $popular = "Top: ";
                foreach ($popularDownloads as $udi => $count) {
                    $popular .= "$udi:$count, ";
                }
                $popular = substr($popular, 0, strlen($popular) - 2);
                $io->writeln($popular);
            }
            $io->newLine();

            $io->writeln('Totals for: ' . $year . ':');
            $io->writeln('Download count: ' . $yearCountTotal);
            $io->writeln('Data Downloaded: ' . round($yearSizeTotal) . ' GB');
            $io->writeln('--------------------------------------------------------------------------------');

            $io->newLine();
        }

        // Total Download stats:
        $io->writeln('All Time Stats:');
        $io->writeln('Download count: ' . $totalDownloads);
        $io->writeln('Data Downloaded: ' . round($totalDownloadSize) . ' GB');

        // Show most popular downloads of all time.
        $popularDownloadsOfAllTime = $this->getTopDatasetsDownloadedByYearAndQuarter(self::NUMBEROFTOPDOWNLOADSTOSHOW);
        $allTimePopular = '';
        $io->writeln('Top ' . self::NUMBEROFTOPDOWNLOADSTOSHOW . ' downloads of all time:');
        foreach ($popularDownloadsOfAllTime as $udi => $count) {
            $allTimePopular .= "$udi:$count, ";
        }
        $allTimePopular = substr($allTimePopular, 0, strlen($allTimePopular) - 2);
        $io->writeln($allTimePopular);

        // Add disclaimer if any datasets had been deleted. (This doesn't apply to gomri currently, but other FOs)
        if ($skipCount > 0) {
            $io->warning("Skipped $skipCount entries as these datasets are no longer available.");
        }

        // Add disclaimer for data cleanup for big data harvest event.
        $io->note('Removed ' . self::HARVEST2019COUNT . ' / ' . self::HARVEST2019DATA . 'GB from 2019Q2 downloads - data harvest occurred');

        return 0;
    }

    /**
     * Returns Year and Quarter given a timestamp.
     *
     * @param \DateTime $timestamp
     * @return array $yearQuarter
     */
    protected function determineQuarter(\DateTime $timestamp): array
    {
        // Quarters are normal calendar quarters (Jan-Mar, Apr-Jun, Jul-Sep, Oct-Dec)
        $year = $timestamp->format('Y');   // returns 4 digit year
        $month = $timestamp->format('n');  // returns 1-12
        $quarter = ceil($month / 3);

        return array("year" => $year, "quarter" => $quarter);
    }

    /**
     * Maintains array of quarter counts.
     *
     * @param \DateTime $timestamp
     * @param array $quarterCounts
     */
    protected function quarterize(\DateTime $timestamp, array &$quarterCounts): void
    {
        $yearAndQuarter = $this->determineQuarter($timestamp);
        $year = $yearAndQuarter['year'];
        $quarter = $yearAndQuarter['quarter'];

        // Initialize
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
     * @param  int|null   $year            The year to get top downloads from.
     * @param  int|null   $quarter         The quarter to get top downloads from.
     * @return array      $topDownloadUdis An associative array of top UDIs with counts as value.
     *
     * @throws Exception If quarter value used other than (1, 2, 3, 4)
     */
    public function getTopDatasetsDownloadedByYearAndQuarter(int $count, int $year = null, int $quarter = null): array
    {
        if ($quarter !== null && !(in_array($quarter, array(1, 2, 3, 4)))) {
            throw new Exception("Bad quarter specified, use 1-4.");
        }

        if ($year === null && $quarter !== null) {
            throw new Exception("If quarter is specified, year must be too.");
        }

        // Create DB compatible strings from DateTime objects.
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
            // entire year
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
        ->setParameter('entityName', 'Pelagos\Entity\Dataset')
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
            $count = $row[1];
            $id = $row['subjectEntityId'];
            $dataset = $this->entityManager->find('\App\Entity\Dataset', $id);
            if ($dataset instanceof Dataset) {
                $udi = $dataset->getUdi();
                $topDownloadUdis[$udi] = $count;
            }
        }
        return $topDownloadUdis;
    }

    /**
     * Generates array of funding-org filter-aware download events.
     *
     * @return array of dataset download events, per FAIR guidelines
     */
    public function getDownloads(): array
    {
        $qb = $this->entityManager->getRepository(LogActionItem::class)->createQueryBuilder('log')
        ->select('log.creationTimeStamp, log.subjectEntityId')
        ->where('log.subjectEntityName = :entityName')
        ->andWhere('log.actionName = :actionName')
        ->orderBy('log.subjectEntityId', 'ASC')
        ->addOrderBy('log.creationTimeStamp', 'ASC')
        ->setParameter('entityName', 'Pelagos\Entity\Dataset')
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

            if (($displayTime === '2014-09-27') or $key === array_key_first($downloads) or ($epochTime - $currentTimeStamp) > 30 or $currentId != $id) {
                $currentTimeStamp = $epochTime;
                $downloadArray[] = array($id, $dateTime);
            }
            $currentId = $id;
        }
        return $downloadArray;
    }
}
