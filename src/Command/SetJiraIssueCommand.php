<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Entity\DatasetSubmission;
use App\Entity\Dataset;

/**
 * This command sets the jira issue on a datset (submisison).
 *
 */
class SetJiraIssueCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:dataset-set-jira';

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
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Sets the Jira issue tracking ticket on a dataset.')
            ->addOption('udi', null, InputOption::VALUE_REQUIRED, 'UDI of dataset to flag as cold stored')
            ->addOption('issueTrackingTicket', null, InputOption::VALUE_REQUIRED, 'The Jira issue-tracking ticket')
            ;
    }

    /**
     * Symfony command execution section.

     * @param InputInterface  $input  Command args.
     * @param OutputInterface $output Output txt.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getOption('udi');
        $issueTrackingTicket= $input->getOption('issueTrackingTicket');

        $io->note("UDI: ($udi)");
        $io->note("Issue tracking ticket: ($issueTrackingTicket)");

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(array('udi' => $udi));
        if ($dataset instanceof Dataset) {
            $io->note('Dataset Found.');
        } else {
            $io->error('Could not find a dataset with UDI ' . $udi);
            return 1;
        }

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!($datasetSubmission instanceof DatasetSubmission)) {
            $io->error('Could not find Dataset Submission in dataset ' . $udi);
            return 1;
        } else {
            $io->note('Submission Found.');

            if ($issueTrackingTicket === '') {
                $datasetSubmission->setIssueTrackingTicket(null);
            } else {
                $datasetSubmission->setIssueTrackingTicket($issueTrackingTicket);
            }
            $this->entityManager->flush();
        }
        $io->success('Done!');
        return 0;
    }
}
