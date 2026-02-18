<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Enum\DatasetLifecycleStatus;
use App\Service\StatisticsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:get-grp-statistics', description: 'Produce GRP report artifacts.')]
class GetGRPStatisticsCommand extends Command
{
    public const NUMBEROFTOPDOWNLOADSTOSHOW = 10;

    /**
     * Common ordered list of lifecycle statuses used for totals and definitions.
     */
    private const STATUS_ORDER = [
        DatasetLifecycleStatus::IDENTIFIED,
        DatasetLifecycleStatus::SUBMITTED,
        DatasetLifecycleStatus::AVAILABLE,
        DatasetLifecycleStatus::RESTRICTED,
        DatasetLifecycleStatus::INCOMPLETE,
        DatasetLifecycleStatus::PENDING,
        DatasetLifecycleStatus::NONE,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StatisticsService $statistics,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();

        $grpDatasetCount = 0;
        $grpDifCount = 0;
        $grpAvailableCount = 0;
        $grpSubmittedCount = 0;
        $grpRestrictedCount = 0;
        $grpIncompleteCount = 0;
        $grpPendingCount = 0;
        $grpNoneCount = 0;
        $grpDatasetColdStorageCount = 0;
        $grpDatasetTotalSize = 0;
        $grpColdStorageDatasetTotalSize = 0;
        $totalPostGrpDatasetsSubmittedByQuarter = [];
        $skipCount = 0;

        foreach ($datasets as $dataset) {
            if (DIF::STATUS_UNSUBMITTED === $dataset->getDif()->getStatus()) {
                continue;
            }
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ('NAS' === $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getShortName()) {
                $datasetLifecycleStatus = $dataset->getDatasetLifecycleStatus();
                ++$grpDatasetCount;
                if ($datasetSubmission instanceof DatasetSubmission) {
                    $this->statistics->quarterize($datasetSubmission->getSubmissionTimeStamp(), $totalPostGrpDatasetsSubmittedByQuarter);

                    if ($datasetSubmission->isDatasetFileInColdStorage()) {
                        ++$grpDatasetColdStorageCount;
                        $grpColdStorageDatasetTotalSize += $datasetSubmission->getColdStorageTotalUnpackedSize() ?? 0;
                    } else {
                        $grpDatasetTotalSize += $datasetSubmission->getFileset()?->getFileSize() ?? 0;
                    }
                }
                if (DatasetLifecycleStatus::AVAILABLE === $datasetLifecycleStatus) {
                    ++$grpAvailableCount;
                }
                if (DatasetLifecycleStatus::RESTRICTED === $datasetLifecycleStatus) {
                    ++$grpRestrictedCount;
                }
                if (DatasetLifecycleStatus::SUBMITTED === $datasetLifecycleStatus) {
                    ++$grpSubmittedCount;
                }
                if (DatasetLifecycleStatus::IDENTIFIED === $datasetLifecycleStatus) {
                    ++$grpDifCount;
                }
                if (DatasetLifecycleStatus::INCOMPLETE === $datasetLifecycleStatus) {
                    ++$grpIncompleteCount;
                }
                if (DatasetLifecycleStatus::PENDING === $datasetLifecycleStatus) {
                    ++$grpPendingCount;
                }
                if (DatasetLifecycleStatus::NONE === $datasetLifecycleStatus) {
                    ++$grpNoneCount;
                }
            }
        }

        $io->writeln("Current GRP Dataset States on " . date('Y-m-d') . ":\n");
        $io->writeln("Total number of GRP Datasets with DIF or submission: $grpDatasetCount");
        $this->printStatusTotals(
            io: $io,
            countsByName: [
                DatasetLifecycleStatus::INCOMPLETE->name => $grpIncompleteCount,
                DatasetLifecycleStatus::PENDING->name => $grpPendingCount,
                DatasetLifecycleStatus::IDENTIFIED->name => $grpDifCount,
                DatasetLifecycleStatus::SUBMITTED->name => $grpSubmittedCount,
                DatasetLifecycleStatus::AVAILABLE->name => $grpAvailableCount,
                DatasetLifecycleStatus::RESTRICTED->name => $grpRestrictedCount,
                DatasetLifecycleStatus::NONE->name => $grpNoneCount,
            ]
        );

        // Definitions of lifecycle states
        $this->printStatusDefinitions(io: $io);

        $io->writeln('Total Size of GRP Datasets: ' . round(($grpDatasetTotalSize + $grpColdStorageDatasetTotalSize) / 1000000000000, 1) . ' TB');
        $io->writeln("Number of GRP Datasets in cold storage: $grpDatasetColdStorageCount");
        $io->writeln('Total size of GRP Datasets in cold storage: ' . round($grpColdStorageDatasetTotalSize / 1000000000000, 1) . " TB\n");

        // GRP submissions by quarter:
        $years = array_keys($totalPostGrpDatasetsSubmittedByQuarter);
        sort($years);
        $minYear = $years[0];
        $maxYear = $years[sizeof($years) - 1];
        $io->writeln("\nGRP Uploads (submissions)\n");
        for ($i = $minYear; $i <= $maxYear; $i++) {
            if (in_array($i, $years)) {
                $io->writeln("Number of GRP datasets submitted $i"
                . ' Q1:' . $totalPostGrpDatasetsSubmittedByQuarter[$i][0]
                . ' Q2:' . $totalPostGrpDatasetsSubmittedByQuarter[$i][1]
                . ' Q3:' . $totalPostGrpDatasetsSubmittedByQuarter[$i][2]
                . ' Q4:' . $totalPostGrpDatasetsSubmittedByQuarter[$i][3]);
            }
        }

        $io->writeln("\nGRP Downloads:\n");
        $downloadSizeByYearAndQuarter = [];
        $anonymousDownloadCountByYearAndQuarter = [];
        $loggedInDownloadCountByYearAndQuarter = [];

        foreach ($this->statistics->getDownloads() as $datasetDownload) {
            $id = $datasetDownload[0];
            $timestamp = $datasetDownload[1];
            $loginType = $datasetDownload[2];

            $yearQuarter = $this->statistics->determineQuarter($timestamp);
            $year = $yearQuarter['year'];
            $quarter = $yearQuarter['quarter'];

            if (!array_key_exists($year, $anonymousDownloadCountByYearAndQuarter)) {
                $anonymousDownloadCountByYearAndQuarter[$year][0] = 0;
                $anonymousDownloadCountByYearAndQuarter[$year][1] = 0;
                $anonymousDownloadCountByYearAndQuarter[$year][2] = 0;
                $anonymousDownloadCountByYearAndQuarter[$year][3] = 0;
                $anonymousDownloadCountByYearAndQuarter[$year][4] = 0;
            }

            if (!array_key_exists($year, $loggedInDownloadCountByYearAndQuarter)) {
                $loggedInDownloadCountByYearAndQuarter[$year][0] = 0;
                $loggedInDownloadCountByYearAndQuarter[$year][1] = 0;
                $loggedInDownloadCountByYearAndQuarter[$year][2] = 0;
                $loggedInDownloadCountByYearAndQuarter[$year][3] = 0;
                $loggedInDownloadCountByYearAndQuarter[$year][4] = 0;
            }

            if (!array_key_exists($year, $downloadSizeByYearAndQuarter)) {
                $downloadSizeByYearAndQuarter[$year][0] = 0;
                $downloadSizeByYearAndQuarter[$year][1] = 0;
                $downloadSizeByYearAndQuarter[$year][2] = 0;
                $downloadSizeByYearAndQuarter[$year][3] = 0;
                $downloadSizeByYearAndQuarter[$year][4] = 0;
            }

            $dataset = $this->entityManager->find(Dataset::class, $id);
            // We may also need to consider using the right size for the external-logged download requests
            // that also get counted here, but end up getting the stub sizes. There aren't many though.
            if ($dataset instanceof Dataset) {
                $size = $dataset->getTotalFileSize();
            } else {
                $size = 0;
                $skipCount++;
            }

            if ($loginType === 'anonymous') {
                $anonymousDownloadCountByYearAndQuarter[$year][$quarter]++;
            } else {
                $loggedInDownloadCountByYearAndQuarter[$year][$quarter]++;
            }
            $downloadSizeByYearAndQuarter[$year][$quarter] += $size / 1000000000;
        }

        // array_keys still onlys dump first level, years, for $array[year][quarter].
        $firstYear = min(array_keys($anonymousDownloadCountByYearAndQuarter));
        $lastYear = max(array_keys($anonymousDownloadCountByYearAndQuarter));

        $totalDownloads = 0;
        $totalDownloadSize = 0;
        for ($year = $firstYear; $year <= $lastYear; $year++) {
            $yearCountTotal = 0;
            $yearSizeTotal = 0;
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $popularDownloads = $this->statistics->getTopDatasetsDownloadedByYearAndQuarter(self::NUMBEROFTOPDOWNLOADSTOSHOW, $year, $quarter);
                $io->writeln(
                    $year
                    . '/Q'
                    . $quarter
                    . ' Logged-in Download Count: '
                    . $loggedInDownloadCountByYearAndQuarter[$year][$quarter]
                    . ' Anonymous DL Count: '
                    . $anonymousDownloadCountByYearAndQuarter[$year][$quarter]
                    . ', ' . 'Total Size (GB): '
                    . round($downloadSizeByYearAndQuarter[$year][$quarter])
                );

                $yearCountTotal += $loggedInDownloadCountByYearAndQuarter[$year][$quarter];
                $yearCountTotal += $anonymousDownloadCountByYearAndQuarter[$year][$quarter];
                $totalDownloads += $loggedInDownloadCountByYearAndQuarter[$year][$quarter];
                $totalDownloads += $anonymousDownloadCountByYearAndQuarter[$year][$quarter];

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
        $popularDownloadsOfAllTime = $this->statistics->getTopDatasetsDownloadedByYearAndQuarter(self::NUMBEROFTOPDOWNLOADSTOSHOW);
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

        return Command::SUCCESS;
    }

    /**
     * Print totals for each lifecycle status in the desired order.
     *
     * @param array<string,int> $countsByName Map of status NAME => count
     */
    private function printStatusTotals(SymfonyStyle $io, array $countsByName): void
    {
        foreach (self::STATUS_ORDER as $status) {
            $name = $status->name;
            $count = $countsByName[$name] ?? 0;
            $io->writeln("Total number of GRP Datasets that are $name: $count");
        }
        $io->newLine();
    }

    /**
     * Print definitions for each DatasetLifecycleStatus.
     */
    private function printStatusDefinitions(SymfonyStyle $io): void
    {
        $io->writeln("\tDefinitions:\n");
        foreach (self::STATUS_ORDER as $status) {
            $io->writeln("\t" . $status->name . ': ' . trim($status->description()));
        }
        $io->newLine();
    }
}
