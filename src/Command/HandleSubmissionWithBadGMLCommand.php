<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\DatasetSubmission;
use App\Entity\Dataset;

/**
 * Back fill all the submitted metadata xml to dataset submission.
 *
 * @see Command
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:handle-bad-gml-submission', description: 'Make the submission accessible in Dataset Review and Submission tool and retrigger filer/hasher.')]
class HandleSubmissionWithBadGMLCommand extends Command
{
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
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addArgument('udi', InputArgument::REQUIRED, 'What is the UDI of the dataset?');
    }

    /**
     * Script to nullify spatial extents and remove distribution point tied to the dataset submission of a dataset.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception When dataset not found.
     * @throws \Exception When datasetSubmission not found.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $udi = $input->getArgument('udi');

        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(['udi' => $udi]);

        if (count($datasets) == 0) {
            throw new \Exception('Could not find a dataset with the udi provided.');
        }

        $dataset = $datasets[0];

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!($datasetSubmission instanceof DatasetSubmission)) {
            throw new \Exception('Could not find Dataset Submission.');
        }

        $datasetSubmission->setSpatialExtent(null);

        //clear distribution points if there is one that has no Data Center
        if (count($datasetSubmission->getDistributionPoints()) === 1) {
            if (null === $datasetSubmission->getDistributionPoints()[0]->getDataCenter()) {
                $datasetSubmission->removeDistributionPoint($datasetSubmission->getDistributionPoints()[0]);
            }
        }

        $this->entityManager->flush();

        $output->writeln('Success: submission ID:' . $datasetSubmission->getId() . ' - Dataset udi: ' . $udi);

        return Command::SUCCESS;
    }
}
