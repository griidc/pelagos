<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:error-checker', description: 'Checks for error status in current files of a dataset')]
class PelagosCheckForErrorStatusCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * An instance of the high-performance memcached server.
     */
    protected \Memcached $memcached;

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
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
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
            ->addOption('udi', null, InputOption::VALUE_OPTIONAL, 'Run only against passed UDI', null)
            ->addOption('summary', 's', InputOption::VALUE_NONE, 'Just show number of errored files per dataset.', null)
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
        $this->memcached = MemcachedAdapter::createConnection('memcached://localhost');

        $io = new SymfonyStyle($input, $output);
        $udi = $input->getOption('udi');
        $short = $input->getOption('summary');
        if ($udi) {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(
                ['udi' => $udi]
            );
        } else {
            $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();
        }

        $io->note('checking: ' . count($datasets) . ' dataset(s).');
        foreach ($datasets as $dataset) {
            /** @var Dataset $dataset */
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $fileset = $datasetSubmission->getFileset();
                if ($fileset instanceof Fileset) {
                    $files = $fileset->getAllFiles()->filter(fn(File $file) => $file->getStatus() === File::FILE_ERROR);
                    $fileCountInError = count($files);
                    if ($fileCountInError > 0) {
                        $io->warning('Dataset ' . $dataset->getUdi() . ' has ' . $fileCountInError . ' files in error.');
                    }
                    if (false === $short) {
                        /** @var File $file */
                        foreach ($files as $file) {
                            $id = $file->getId();
                            $twins = $this->twinFinder($dataset, $file, $this->entityManager, $io);
                            $io->note("known dataset file: ($id): " . $file->getPhysicalFilePath());
                            foreach ($twins as $twin) {
                                $twinId = $twin->getId();
                                $io->warning("same hash ($twinId): " . $twin->getPhysicalFilePath());
                            }
                        }
                    }
                }
            }
        }
        return Command::SUCCESS;
    }

    /**
     * Method finds identical files by hash.
     *
     * @param Dataset       $dataset Original dataset that owns fileset.
     * @param File          $file    The file to look for matches against.
     * @param EntityManager $em      Doctrine entity manager.
     * @param SymfonyStyle  $io      Symfony IO style used for user-facing messages.
     *
     * @return array Matching files.
     */
    protected function twinFinder(Dataset $dataset, File $file, EntityManager $em, SymfonyStyle $io): array
    {
        $hashMatches = [];
        $originalFile = $unalteredOriginalFile = $file->getPhysicalFilePath();
        if (!str_starts_with((string) $originalFile, '/')) {
            $originalFile = $this->dataStoreDirectory . '/' . $originalFile;
        }
        if (file_exists($originalFile)) {
            $hash = $this->smartSha256Hash($originalFile);

            // find other files in fileset to find match, but only matches in in live dataset submission's fileset.
            $dql = "SELECT f FROM \App\Entity\File f
                WHERE f.fileset = :fileset
                AND f.physicalFilePath <> :orig";
            $query = $em->createQuery($dql);
            $query->setParameter('fileset', $dataset->getDatasetSubmission()->getFileset());
            $query->setParameter('orig', $unalteredOriginalFile);
            $matches = $query->getResult();

            foreach ($matches as $match) {
                $matchesPath = $match->getPhysicalFilePath();
                if (!str_starts_with((string) $matchesPath, '/')) {
                    $matchesPath = $this->dataStoreDirectory . '/' . $matchesPath;
                }
                if (file_exists($matchesPath)) {
                    $hash2 = $this->smartSha256Hash($matchesPath);
                    if ($hash == $hash2) {
                        $hashMatches[] = $match;
                    }
                } else {
                    $io->warning("A file in active fileset not found on disk: " . $matchesPath . "\n");
                }
            }
        } else {
            $io->warning("Original file not found on disk! " . $originalFile . "\n");
        }
        return $hashMatches;
    }

    /**
     * Returns sha256sum of file, checking for previous work first.
     *
     * @param String $filename The filename of what we want to get a hash of.
     *
     * @return String $hash The sha256 of the file, either from cache or generated.
     */
    protected function smartSha256Hash($filename): string
    {
        $hash = $this->memcached->get($filename);
        if (false === $hash) {
            $hash = hash_file("sha256", $filename);
            $this->memcached->set($filename, $hash);
        }
        return $hash;
    }
}
