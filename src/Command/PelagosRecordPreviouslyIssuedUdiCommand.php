<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\Udi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'pelagos:record-previous-udi',
    description: 'Record a legacy previously-issued UDIs to prevent re-use.',
)]
class PelagosRecordPreviouslyIssuedUdiCommand extends Command
{
    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager a Doctrine EntityManager
     */
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('udi', InputArgument::REQUIRED, 'UDI of prior dataset to record')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');

        $issuedUdi = $this->entityManager->getRepository(Udi::class)->findOneBy(['uniqueDataIdentifier' => $udi]);
        if ($issuedUdi instanceof Udi) {
            $io->warning('UDI ' . $udi . ' already recorded.');
            return Command::SUCCESS;
        }

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
        if ($dataset instanceof Dataset) {
            $io->info('Dataset with UDI ' . $udi . ' currently exists.');
        }

        $issuedUdi = new Udi($udi);
        $this->entityManager->persist($issuedUdi);
        $this->entityManager->flush();

        $io->success('UDI ' . $udi . ' recorded successfully.');

        return Command::SUCCESS;
    }
}
