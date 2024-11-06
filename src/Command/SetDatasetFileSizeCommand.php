<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetDatasetFileSizeCommand extends Command
{
    protected static $defaultName = 'pelagos:set-dataset-filesize';
    protected static $defaultDescription = 'Set dataset filesize for all datasets.';

    /**
     * Class constructor for dependency injection.
     */
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This will set the file size for all datasets, continue? [y/N]', false);

        if ($helper->ask($input, $output, $question)) {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();

            $progressBar = new ProgressBar($output, count($datasets));
            $progressBar->start();

            foreach ($datasets as $dataset) {
                /** @var Dataset $dataset */
                $datasetSubmission = $dataset->getDatasetSubmission();
                if ($datasetSubmission instanceof DatasetSubmission) {
                    $datasetSubmission->setDatasetFileSize($dataset->getTotalFileSize());
                    $progressBar->advance();
                }
            }
            $progressBar->finish();
            $io->note("Flushing data...");
            $this->entityManager->flush();

            $io->success("DONE!");

            return Command::SUCCESS;
        }

        $io->warning("ABORTED");

        return Command::INVALID;
    }
}
