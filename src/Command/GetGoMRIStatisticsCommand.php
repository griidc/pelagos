<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use DateTime;

class GetGoMRIStatisticsCommand extends Command
{
    protected static $defaultName = 'pelagos:get-gomri-statistics';
    protected static $defaultDescription = 'Produce GoMRI report artifacts.';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
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

        foreach ($datasets as $dataset) {
            /** @var Dataset $dataset */
            $datasetCount++;
            $udi = $dataset->getUdi();
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getShortName() === 'GoMRI') {
                $gomriDatasetCount++;
                if ($datasetSubmission instanceof DatasetSubmission) {
                    if ($datasetSubmission->getSubmissionTimeStamp()->format('U') >= DateTime::createFromFormat('d/m/Y', '01/01/2021', new \DateTimeZone('America/Chicago'))->format('U')) {
                        $totalGomriDatasetSubmittedSince2021Count++;
                    }
                    if ($datasetSubmission->isDatasetFileInColdStorage()) {
                        $gomriDatasetColdStorageCount++;
                        $gomriColdStorageDatasetTotalSize += $datasetSubmission->getColdStorageTotalUnpackedSize() ?? 0;
                    } else {
                        $gomriDatasetTotalSize += $datasetSubmission->getFileset()?->getFileSize() ?? 0;
                    }
                }
            }

            if ($datasetSubmission instanceof DatasetSubmission) {
                if ($datasetSubmission->getSubmissionTimeStamp()->format('U') >= DateTime::createFromFormat('d/m/Y', '01/01/2021', new \DateTimeZone('America/Chicago'))->format('U')) {
                    $totalDatasetSubmittedSince2021Count++;
                }
            }
        }

        $io->writeln("Total GRIIDC Dataset Count: $datasetCount");
        $io->writeln("Total number of GoMRI Datasets: $gomriDatasetCount");
        $io->writeln("Total Size of GoMRI Datasets: " . round(($gomriDatasetTotalSize + $gomriColdStorageDatasetTotalSize)/1000000000000, 1) . ' TB');
        $io->writeln("Number of GoMRI Datasets in cold storage: $gomriDatasetColdStorageCount");
        $io->writeln("Total size of GoMRI Datasets in cold storage: " . round($gomriColdStorageDatasetTotalSize/1000000000000, 1) . ' TB');
        $io->writeln("Total number of GoMRI datasets submitted since 2021-01-01 until current date " . $totalGomriDatasetSubmittedSince2021Count);
        $io->writeln("Total number of datasets (all data including GoMRI) submitted since 2021-01-01 until current date " . $totalDatasetSubmittedSince2021Count);

        return 0;
    }
}
