<?php

namespace App\Command;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:get-gml',
    description: 'Add a short description for your command',
)]
class PelagosGetGmlCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('udi', InputArgument::REQUIRED, 'UDI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');

        $datasetRepository = $this->entityManager->getRepository(Dataset::class);

        /** @var Dataset $dataset */
        $dataset = $datasetRepository->findOneBy(['udi' => $udi]);

        $geom = $dataset->getSpatialExtentGeometry();

        print($geom);

        return Command::SUCCESS;
    }
}
