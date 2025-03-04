<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:missing-file-detector', description: 'Checks for missing files on a dataset.')]
class PelagosCheckForMissingFilesCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * A Pelagos Datastore instance for checking file existence.
     *
     * @var Datastore $datastore
     */
    protected $datastore;

    /**
     * Storage location for submitted+ datasets.
     *
     * @var string $dataStoreDirectory
     */
    protected $dataStoreDirectory;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager      A Doctrine EntityManager.
     * @param FilesystemInterface    $datastoreFlysystem A filesystem interface.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Datastore $datastore
    ) {
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
        $this->dataStoreDirectory = $_ENV['DATA_STORE_DIRECTORY'];
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
            ->addOption('udi', null, InputOption::VALUE_OPTIONAL, 'Run only against passed UDI', null);
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
        $udi = $input->getOption('udi');

        if ($udi) {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(
                ['udi' => $udi]
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
                    $files = $fileset->getAllFiles()->filter(fn(File $file) => $file->getStatus() === File::FILE_DONE);
                    $fileCount = count($files);
                    $io->writeln('Dataset ' . $dataset->getUdi() . ' has ' . $fileCount . ' files.' . "\n");
                    /** @var File $file */
                    foreach ($files as $file) {
                        $filePath = $file->getPhysicalFilePath();
                        if (!$this->datastore->has($filePath)) {
                            $formattedPath = $this->dataStoreDirectory . '/' . $filePath;
                            $io->error("missing file at: " . $formattedPath . "\n");
                        }
                    }
                }
            }
        }
        return Command::SUCCESS;
    }
}
