<?php

namespace App\Command;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:get-size',
    description: 'Get disk size of dataset by udi',
)]
class PelagosGetSizeCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        // Call the parent constructor
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('udi', InputArgument::REQUIRED, 'Dataset UDI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');

        $datasetRepository = $this->entityManager->getRepository(Dataset::class);

        /** @var Dataset $dataset */
        $dataset = $datasetRepository->findOneBy(['udi' => $udi]);
        $size = $dataset->getTotalFileSize() ?? 0;
        if ($dataset->isColdStored()) {
            $size = $dataset->getDatasetSubmission()->getColdStorageTotalUnpackedSize() ?? 0;
        } else {
            $size = $dataset->getTotalFileSize() ?? 0;
        }

        $io->writeln($udi . ',' . $size);
        $io->success('Done!');

        return Command::SUCCESS;
    }
}
