<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DeletedUdi;
use App\Entity\Person;
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
        $systemPerson = $this->entityManager->find(Person::class, 0);

        $deletedUdi = $this->entityManager->getRepository(DeletedUdi::class)->findOneBy(['udi' => $udi]);
        if ($deletedUdi instanceof DeletedUdi) {
            $io->warning('UDI ' . $udi . ' already marked as deleted.');
            return Command::SUCCESS;
        }

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
        if ($dataset instanceof Dataset) {
            $io->error('Dataset with UDI ' . $udi . ' currently exists. Cannot record as deleted.');
            return Command::FAILURE;
        } else {
            $deletedUdi = new DeletedUdi();
            $deletedUdi->setUDI($udi);
            $deletedUdi->setCreator($systemPerson);
            $this->entityManager->persist($deletedUdi);
            $this->entityManager->flush();

            $io->success('Deleted UDI ' . $udi . ' recorded successfully.');

            return Command::SUCCESS;
        }
    }
}
