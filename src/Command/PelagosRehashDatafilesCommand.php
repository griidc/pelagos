<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'pelagos:rehash-datafiles',
    description: 'This command rehashes the datafiles of an UDI and updates the submission.',
)]
class PelagosRehashDatafilesCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * The message bus for dispatching the filer message.
     *
     * @var MessageBusInterface $messageBus
     */
    protected $messageBus;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param MessageBusInterface    $messageBus    The messenger bus.
     */
    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('udi', InputArgument::OPTIONAL, 'dataset udi')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');
        $nudi = str_replace(':', '.', $udi);

        $io->note("Re-hashing UDI files:$udi");

        $systemPerson = $this->entityManager->find(Person::class, 0);

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
        if (!$dataset instanceof Dataset) {
            $io->error("Dataset not found for $udi");
            return 1;
        }

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!$datasetSubmission instanceof DatasetSubmission) {
            $io->error("Dataset Submission not found for $udi");
            return 1;
        }

        $fileset = $datasetSubmission->getFileset();
        if (!$fileset instanceof Fileset) {
            $io->error("Fileset not found for $udi");
            return 1;
        }


        $files = $fileset->getAllFiles();

        $dirtyBit = false;
        /** @var File $file */
        foreach ($files as $file) {
            $oldhash = $file->getFileSha256Hash();
            $newhash = hash_file('sha256', '/san/data/store/' . $file->getPhysicalFilePath());
            if ($oldhash !== $newhash) {
                $dirtyBit = true;
                $file->setFileSha256Hash($newhash);
                $io->writeln("Updated hash for file: " . $file->getFilePathName() . " from $oldhash to $newhash");
                $this->entityManager->persist($file);
            } else {
                $io->writeln("Not updating hash for file: " . $file->getFilePathName() . " as it is the same");
            }
        }

        if ($dirtyBit) {
            $this->entityManager->flush();
        }

        $io->success(sprintf('Hashes updated for %s.', $udi));

        return 0;

        return Command::SUCCESS;
    }
}
