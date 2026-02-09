<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Service\StatisticsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:get-gomri-statistics', description: 'Produce GoMRI report artifacts.')]
class GetGoMRIStatisticsCommand extends Command
{
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
        private readonly EntityManagerInterface $entityManager,
        private readonly StatisticsService $statistics,
    ) {
        parent::__construct();
    }

    /**
     * Exposes the entity manager to subclasses while keeping encapsulation.
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function configure(): void
    {
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
                    $this->statistics->quarterize($datasetSubmission->getSubmissionTimeStamp(), $totalPostGomriDatasetsSubmittedByQuarter);

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

        // Deal with the data harvest of 2019Q2. (were not logged-in)
        $anonymousDownloadCountByYearAndQuarter[2019][2] -= self::HARVEST2019COUNT;
        $downloadSizeByYearAndQuarter[2019][2] -= self::HARVEST2019DATA;

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

        // Add disclaimer for data cleanup for big data harvest event.
        $io->note('Removed ' . self::HARVEST2019COUNT . ' / ' . self::HARVEST2019DATA . 'GB from 2019Q2 downloads - data harvest occurred');

        return Command::SUCCESS;
    }


}
