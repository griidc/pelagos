<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;

class AdjustAcceptedDateCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:adjust-accepted-date';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Sets the accepted to first accepted dateset per dataset.')
        ;
    }

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

     /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $datasets = $this->entityManager
            ->getRepository(Dataset::class)
            ->findAll();

        $datasetCount = 0;

        foreach ($datasets as $dataset) {
            $datasetStatus = $dataset->getDatasetStatus();
            $udi = $dataset->getUdi();

            if ($datasetStatus === Dataset::DATASET_STATUS_ACCEPTED) {
                // echo "$udi,";

                $datasetSubmissionHistory = $dataset->getDatasetSubmissionHistory();
                $lastAcceptedDate = $dataset->getAcceptedDate();

                // $mydate = $lastAcceptedDate->format('c');
                // echo "$mydate,";

                foreach ($datasetSubmissionHistory as $datasetSubmission) {
                    $lastDatasetStatus = $datasetSubmission->getDatasetStatus();
                    if ($lastDatasetStatus === Dataset::DATASET_STATUS_ACCEPTED) {
                        $newAcceptedDate = $datasetSubmission->getModificationTimeStamp(true);
                    }
                }

                // $mydate = $newAcceptedDate->format('c');
                // echo "$mydate \n";

                if ($lastAcceptedDate <> $newAcceptedDate) {
                    //echo "$udi is different\n";

                    $dataset->setAcceptedDate($newAcceptedDate);

                    $this->entityManager->persist($dataset);
                    $this->entityManager->flush($dataset);

                    $datasetCount++;
                }
            }
        }

        $io->note("Proccesed $datasetCount datasets");

        $io->success('Done, all dates updated!');

        return 0;
    }
}
