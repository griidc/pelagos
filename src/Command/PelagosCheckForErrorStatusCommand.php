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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PelagosCheckForErrorStatusCommand extends Command
{
    protected static $defaultName = 'pelagos:error-checker';
    protected static $defaultDescription = 'Checks for error status in current files of a dataset';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * An instance of the high-performance memcached server.
     *
     * @var Memcached $memcached
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
        EntityManagerInterface $entityManager,
        String $dataStoreDirectory
    ) {
        $this->entityManager = $entityManager;
        $this->dataStoreDirectory = $dataStoreDirectory;

        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('udi', InputArgument::OPTIONAL, 'Run only against passed UDI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->memcached = MemcachedAdapter::createConnection('memcached://localhost');

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
            /** @var Dataset $dataset */
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $fileset = $datasetSubmission->getFileset();
                if ($fileset instanceof Fileset) {
                    $files = $fileset->getAllFiles()->filter(function (File $file) {
                        return $file->getStatus() === File::FILE_ERROR;
                    });
                    $fileCountInError = count($files);
                    if ($fileCountInError > 0) {
                        $io->warning('Dataset ' . $dataset->getUdi() . ' has the following ' . $fileCountInError . ' files in error.');
                    }
                    /** @var File $file */
                    foreach ($files as $file) {
                        $id = $file->getId();
                        $twins = $this->twinFinder($dataset, $file, $this->entityManager, $io);
                        $io->writeln("found on-disk ($id): " . $file->getPhysicalFilePath());
                        if (count($twins) > 0) {
                            foreach ($twins as $twin) {
                                $twinId = $twin->getId();
                                $io->writeln("same hash ($twinId): " . $twin->getPhysicalFilePath());
                            }
                        }
                    }
                }
            }
        }
        return 0;
    }

    protected function twinFinder(Dataset $dataset, File $file, EntityManager $em, SymfonyStyle $io): Array
    {
        $hashMatches = array();
        $originalFile = $unalteredOriginalFile = $file->getPhysicalFilePath();
        $originalFileId = $file->getId();
        if (substr($originalFile,0,1) != '/') { $originalFile = $this->dataStoreDirectory . '/' . $originalFile; }
        $io->note("originalFile ($originalFileId): $originalFile");
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
                if (substr($matchesPath,0,1) != '/') { $matchesPath = $this->dataStoreDirectory . '/' . $matchesPath; }
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
     */
    protected function smartSha256Hash($filename)
    {
        $hash = $this->memcached->get($filename);
        if ($hash) {
            return $hash;
        } else {
            // I hardcoded to sha256 otherwise hashtype would have to be part
            // of what is stored in cache. Named method accordingly.
            $hash = hash_file("sha256", $filename);
            $this->memcached->set($filename, $hash);
        }
    }
}
