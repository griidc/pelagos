<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Message\ZipDatasetFiles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Command to re-queue zip files for a dataset.
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:re-queue-zip', description: 'Re-queue zip files by UDI')]
class PelagosReQueueZipCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Symfony messenger bus interface.
     *
     * @var MessageBusInterface $messageBus
     */
    protected $messageBus;

    /**
     * The command configuration.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('udi', InputArgument::REQUIRED, 'What is the UDI of the dataset?');
        ;
    }

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param MessageBusInterface    $messageBus    Symfony messenger bus interface.
     */
    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        parent::__construct();
    }

    /**
     * Symfony command execute section.
     *
     * @param InputInterface  $input  Required by Command.
     * @param OutputInterface $output Required by Command.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');
        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);

        if ($dataset) {
            $datasetSubmission = $dataset->getLatestDatasetReview();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $zipFiles = new ZipDatasetFiles($datasetSubmission->getId());
                $this->messageBus->dispatch($zipFiles);
            }
        } else {
            $io->error('No such dataset exists');
        }

        $io->success('Zip files re-queued for the dataset');

        return Command::SUCCESS;
    }
}
