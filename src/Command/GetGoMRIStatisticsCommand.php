<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Repository\LogActionItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetGoMRIStatisticsCommand extends Command
{
    protected static $defaultName = 'pelagos:get-gomri-statistics';
    protected static $defaultDescription = 'Produce GoMRI report artifacts.';

    /**
     * Class constructor for dependency injection.
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LogActionItemRepository $logActionItemRepository,
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
        for ($i = $minYear; $i <= $maxYear; $i++) {
            if (in_array($i, $years)) {
                $io->writeln("Number of GoMRI datasets submitted $i"
                . ' Q1:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][0]
                . ' Q2:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][1]
                . ' Q3:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][2]
                . ' Q4:' . $totalPostGomriDatasetsSubmittedByQuarter[$i][3]);
            }
        }

        $io->writeln("Total GoMRI Downloads:");
        $downloadSizeByYear = [];
        $downloadCountByYear = [];
        foreach ($this->logActionItemRepository->getDownloads() as $datasetDownload) {
            $id = $datasetDownload[0];
            $timestamp = $datasetDownload[1];
            $year = substr($timestamp, 0, 4);
            if (!array_key_exists($year, $downloadCountByYear)) {
                $downloadCountByYear[$year] = 0;
            }
            if (!array_key_exists($year, $downloadSizeByYear)) {
                $downloadSizeByYear[$year] = 0;
            }

            $dataset = $this->entityManager->find('\App\Entity\Dataset', $id);
            $size = $dataset->getTotalFileSize();
            $udi = $dataset->getUdi();

            $downloadCountByYear[$year]++;
            $downloadSizeByYear[$year] += $size / 1000000000;
            //print "$timestamp,$id,$udi,$size\n";
        }

        $firstYear = min(array_keys($downloadCountByYear));
        $lastYear = max(array_keys($downloadCountByYear));

        for ($i = $firstYear; $i <= $lastYear; $i++) {
            $io->writeln($i . ' Download Count: ' . $downloadCountByYear[$i] . ', ' . 'Total Size (GB): ' . round($downloadSizeByYear[$i]));
        }

        return 0;
    }

    /**
     * Maintains array of quarter counts.
     *
     * @param \DateTime $timestamp
     * @param array $quarterCounts
     */
    protected function quarterize(\DateTime $timestamp, array &$quarterCounts): void
    {
        // Quarters are strict calendar quarters.
        // Q1: Jan 1 - Mar 31
        // Q2: Apr 1 - Jun 30
        // Q3: Jul 1 - Sep 30
        // Q4: Oct 1 - Dec 31
        $year = $timestamp->format('Y');   // returns 4 digit year
        $month = $timestamp->format('n');  // returns 1-12
        $quarter = ceil($month / 3);

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
}
