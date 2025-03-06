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
use App\Entity\Person;

/**
 * This command marks a dataset as cold-stored.
 *
 * This command saves the original hash and size, replaces the datafile with a
 * supplied manifest file, then re-triggers filer/hasher in an expected way for
 * a replaced datafile.
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:dataset-flag-coldstorage', description: 'Marks specified dataset as cold-stored and updates fileset with provided files')]
class ColdStorageFlagCommand extends Command
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
            ->addOption('udi', null, InputOption::VALUE_REQUIRED, 'UDI of dataset to flag as cold stored')
            ->addOption('originalfilesize', null, InputOption::VALUE_REQUIRED, 'Original file size')
            ->addOption('originalfilehash', null, InputOption::VALUE_REQUIRED, 'Original sha256 hash')
            ->addOption('originalfilename', null, InputOption::VALUE_REQUIRED, 'Original file name')
            ->addOption('unpackedfilecount', null, InputOption::VALUE_REQUIRED, 'Unpacked file count')
            ->addOption('unpackedbytecount', null, InputOption::VALUE_REQUIRED, 'Unpacked byte count')
            ;
    }

    /**
     * Symfony command execution section.

     * @param InputInterface  $input  Command args.
     * @param OutputInterface $output Output txt.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $systemPerson = $this->entityManager->find(Person::class, 0);

        $io = new SymfonyStyle($input, $output);
        $udi = $input->getOption('udi');
        $originalFileSize = $input->getOption('originalfilesize');
        $originalFileHash = $input->getOption('originalfilehash');
        $originalFileName = $input->getOption('originalfilename');
        $unpackedFileCount = $input->getOption('unpackedfilecount');
        $unpackedByteCount = $input->getOption('unpackedbytecount');

        $io->note("UDI: ($udi)");
        $io->note("Original Size: ($originalFileSize)");
        $io->note("Original Hash: ($originalFileHash)");
        $io->note("Original File Name: ($originalFileName)");
        $io->note("Unpacked File Count: ($unpackedFileCount)");
        $io->note("Unpacked Byte Count: ($unpackedByteCount)");
        $io->note("Attempting to flag $udi as Cold Stored.");

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
        if ($dataset instanceof Dataset) {
            $io->note('Dataset Found.');
        } else {
            $io->error('Could not find a dataset with UDI ' . $udi);
            return Command::FAILURE;
        }

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!($datasetSubmission instanceof DatasetSubmission)) {
            $io->error('Could not find Dataset Submission in dataset ' . $udi);
            return Command::FAILURE;
        } else {
            $io->note('Submission Found.');

            $datasetSubmission->setModifier($systemPerson);
            $datasetSubmission->setDatasetFileColdStorageAttributes($originalFileSize, $originalFileHash, $originalFileName, $unpackedFileCount, $unpackedByteCount);

            $this->entityManager->flush();
        }

        $io->success('Done!');

        return Command::SUCCESS;
    }
}
