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
 * This command sets the issue ticket on a datset (submisison).
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:dataset-set-issue-ticket', description: 'Sets the issue tracker ticket on a dataset.')]
class SetIssueTrackerTicketCommand extends Command
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
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addOption('csvfile', null, InputOption::VALUE_REQUIRED, 'Filename of csv containing UDI, ticket')
            ;
    }

    /**
     * Symfony command execution section.
     *
     * @param InputInterface  $input  Command args.
     * @param OutputInterface $output Output txt.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = $input->getOption('csvfile');

        if (!file_exists($filename)) {
            $io->error('File not found: ' . $filename);
            return Command::FAILURE;
        }

        if (($fileHandle = fopen($filename, "r")) !== false) {
            while (($data = fgetcsv($fileHandle, 100, ",")) !== false) {
                if (!array_key_exists(1, $data)) {
                    $io->warning("Bad input, skipping entry");
                    continue;
                }
                $udi = trim((string) $data[0]);
                $issueTrackingTicket = trim((string) $data[1]);
                $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
                if (!($dataset instanceof Dataset)) {
                    $io->warning("Could not find a dataset with UDI ($udi)");
                    continue;
                } else {
                    if (empty($issueTrackingTicket)) {
                        $io->warning("No Issue Tracking Ticket in CSV for Dataset $udi, skipping.");
                    } else {
                        $io->note("Setting Issue Tracking Ticket: $issueTrackingTicket for Dataset $udi.");
                        $dataset->setIssueTrackingTicket($issueTrackingTicket);
                    }
                }
            }
            $this->entityManager->flush();
            fclose($fileHandle);
        }
        $io->success('Done!');
        return Command::SUCCESS;
    }
}
