<?php

namespace App\Command;

use App\Entity\DeletedUdi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'pelagos:record-deleted-udi',
    description: 'Record a previously-deleted UDIs to prevent re-use.',
)]
class PelagosRecordDeletedUdiCommand extends Command
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
            ->addArgument('udi', InputArgument::REQUIRED, 'UDI of previously-deleted dataset')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');

        if ($udi) {
            $deletedUdi = new DeletedUdi();
            $deletedUdi->setUDI($udi);
            $this->entityManager->persist($deletedUdi);
            $this->entityManager->flush();
        }

        $io->success('Deleted UDI ' . $udi . ' recorded successfully.');

        return Command::SUCCESS;
    }
}
