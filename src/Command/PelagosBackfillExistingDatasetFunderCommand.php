<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\Funder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * Configure function to allow for options and parameters.
     */
    protected function configure(): void
    {
        $this
            ->setDescription("This command will set all dataset's funder to it's upstream Funding Org's default funder. ")
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing funders?')
        ;
    }

    /**
     * The symfony command execute function.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Dataset Funder Backfiller');
        $overwrite = $input->getOption('overwrite') ? true : false;

        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();

        $io->section('Starting Backfill');
        foreach ($datasets as $dataset) {
            /** @var Dataset $dataset */
            $fundingOrg = $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization();
            $defaultFunder = $fundingOrg->getDefaultFunder();
            $existingFunders = $dataset->getFunders();

            // Set each dataset's funder, but only if it's affiliated FO doesn't have a null Funder.
            if ($defaultFunder instanceof Funder) {
                // Clear existing funders if overwrite flag is set.
                if ($overwrite === true) {
                    foreach ($existingFunders as $funder) {
                        $dataset->removeFunder($funder);
                    }
                }
                // Don't add a duplicate Funder.
                if ($existingFunders->contains($defaultFunder)) {
                    $io->warning("Not setting Funder on " . $dataset->getUdi() . " because it's already set.");
                } else {
                    $dataset->addFunder($defaultFunder);
                }
            } else {
                $io->warning("Not setting Funder on " . $dataset->getUdi() . " because FO " . $fundingOrg->getName() . " has no default funder set.");
            }

        }

        $this->entityManager->flush();
        $io->newLine(3);
        $io->success('Done!');

        return 0;
    }
}
