<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class PelagosCheckForMissingFilesCommand extends Command
{
    protected static $defaultName = 'pelagos:missing-file-detector';
    protected static $defaultDescription = 'Checks for missing files on a dataset.';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * A Symfony filesystem instance for checking file existence.
     *
     * @var Filesystem $filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Storage location for submitted+ datasets.
     *
     * @var string $dataStoreDirectory
     */
    protected $dataStoreDirectory;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        String $dataStoreDirectory
    ) {
        $this->entityManager = $entityManager;
        $this->dataStoreDirectory = $dataStoreDirectory;
        $this->filesystem = new Filesystem;
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    /**
     * Configure function to allow for options and parameters.
     */
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('udi', InputArgument::OPTIONAL, 'Run only against passed UDI')
        ;
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

        if ($udi) {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(
                array('udi' => $udi)
            );
        } else {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();
        }

        $io->note('checking: ' . count($datasets) . ' dataset(s).');
        foreach ($datasets as $dataset) {
            $checkedUdi = $dataset->getUdi();
            $io->note("checking dataset: $checkedUdi\n");
            /** @var Dataset $dataset */
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $fileset = $datasetSubmission->getFileset();
                if ($fileset instanceof Fileset) {
                    $files = $fileset->getAllFiles()->filter(function (File $file) {
                        return $file->getStatus() !== File::FILE_ERROR;
                    });
                    $fileCount = count($files);
                    $io->writeln('Dataset ' . $dataset->getUdi() . ' has ' . $fileCount . ' files.' . "\n");
                    /** @var File $file */
                    foreach ($files as $file) {
                        $originalFile = $file->getPhysicalFilePath();
                        if (substr($originalFile, 0, 1) != '/') {
                            $originalFile = $this->dataStoreDirectory . "/$originalFile";
                        }
                        if (!$this->filesystem->exists($originalFile)) {
                            $io->error("missing file at: $originalFile.\n");
                        }
                    }
                }
            }
        }
        return 0;
    }
}
