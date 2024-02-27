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
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('start', InputArgument::OPTIONAL, 'Starting date for DL count')
            ->addArgument('end', InputArgument::OPTIONAL, 'Ending date for DL count')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $startEpoch = strtotime($input->getArgument('start'));
        $endEpoch = strtotime($input->getArgument('end'));

        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();

        $datasetCount = 0;
        $gomriDatasetCount = 0;
        $gomriDatasetColdStorageCount = 0;
        $gomriDatasetTotalSize = 0;
        $gomriColdStorageDatasetTotalSize = 0;
        $totalGomriDatasetSubmittedSince2021Count = 0;
        $totalDatasetSubmittedSince2021Count = 0;

        foreach ($datasets as $dataset) {
            if (DIF::STATUS_UNSUBMITTED === $dataset->getDif()->getStatus()) {
                continue;
            }
            ++$datasetCount;
            $udi = $dataset->getUdi();
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ('GoMRI' === $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getShortName()) {
                ++$gomriDatasetCount;
                if ($datasetSubmission instanceof DatasetSubmission) {
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

        $io->writeln("Total GRIIDC Dataset Count: $datasetCount");
        $io->writeln("Total number of GoMRI Datasets: $gomriDatasetCount");
        $io->writeln('Total Size of GoMRI Datasets: ' . round(($gomriDatasetTotalSize + $gomriColdStorageDatasetTotalSize) / 1000000000000, 1) . ' TB');
        $io->writeln("Number of GoMRI Datasets in cold storage: $gomriDatasetColdStorageCount");
        $io->writeln('Total size of GoMRI Datasets in cold storage: ' . round($gomriColdStorageDatasetTotalSize / 1000000000000, 1) . ' TB');
        $io->writeln('Total number of GoMRI datasets submitted since 2021-01-01 until current date ' . $totalGomriDatasetSubmittedSince2021Count);
        $io->writeln('Total number of datasets (all data including GoMRI) submitted since 2021-01-01 until current date ' . $totalDatasetSubmittedSince2021Count);

        if (false != $startEpoch and false != $endEpoch) { // false on strtotime fail
            $startDateTime = new \DateTime();
            // will assume DST-aware central time if not specified.
            $startDateTime->setTimezone(new \DateTimeZone('America/Chicago'));
            $startDateTime->setTimestamp($startEpoch);
            $dbFormatStartTime = $startDateTime->format('Y-m-d H:i:sO');

            $endDateTime = new \DateTime();
            $endDateTime->setTimezone(new \DateTimeZone('America/Chicago'));
            $endDateTime->setTimestamp($endEpoch);
            $dbFormatEndTime = $endDateTime->format('Y-m-d H:i:sO');

            $io->writeln("Total GoMRI Downloads from $dbFormatStartTime to $dbFormatEndTime: "
                . $this->logActionItemRepository->countDownloads(
                    $startDateTime,
                    $endDateTime
                ));
        } else {
            $io->writeln('Total GoMRI Downloads: ' . $this->logActionItemRepository->countDownloads());
        }

        return 0;
    }
}
