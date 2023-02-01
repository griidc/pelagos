<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\Funder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command Class to assign dataset's Funder based on associated FO's default funder.
 */
class PelagosBackfillExistingDatasetFunderCommand extends Command
{
    protected static $defaultName = 'pelagos:backfill-dataset-funder';

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
     * Configure function to allow for options and parameters.
     */
    protected function configure()
    {
        $this
            ->setDescription("This command will set all dataset's funder to it's upstream Funding Org's default funder. ")
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing funders?')
        ;
    }

    /**
     * The symfony command execute function.
     *
     * @param InputInterface  $input  The Symfony Console Input.
     * @param OutputInterface $output The Symfony Console Output.
     *
     * @return int Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Dataset Funder Backfiller');

        $overwrite = $input->getOption('overwrite') ? true : false;

        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();

        $io->section('Starting Backfill');

        $progressBar = new ProgressBar($output, count($datasets));
        $progressBar->start();

        foreach ($datasets as $dataset) {
            /** @var Dataset $dataset */
            $fundingOrg = $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization();
            $defaultFunder = $fundingOrg->getDefaultFunder();

            // set dataset's funder
            if ($defaultFunder instanceof Funder) {
                $existingFunders = $dataset->getFunders();
                // Clear existing funders if overwrite flag is set.
                if ($overwrite === true) {
                    foreach ($existingFunders as $funder) {
                        $dataset->removeFunder($funder);
                    }
                }

                if ($existingFunders->contains($defaultFunder)) {
                    $io->warning("Not setting Funder on " . $dataset->getUdi() . " because it's already set.");
                } else {
                    $dataset->addFunder($defaultFunder);
                }
            } else {
                $io->warning("Not setting Funder on " . $dataset->getUdi() . " because FO " . $fundingOrg->getName() . " has no default funder set.");
            }

        }
        $progressBar->finish();
        $io->newLine(3);

        $io->section('Flushing');
        $this->entityManager->flush();

        $io->success('Done!');

        return 0;
    }
}
