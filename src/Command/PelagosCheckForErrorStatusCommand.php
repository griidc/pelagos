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
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;

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
                        $twins = $this->twins($dataset, $file, $this->entityManager);
                        $io->writeln('on-disk: ' . $file->getPhysicalFilePath());
                        if (count($twins) > 1) {
                            foreach ($twins as $twin) {
                                $io->writeln('same hash: ' . $twin->getPhysicalFilePath());
                            }
                        }
                    }
                }
            }
        }
        return 0;
    }

    protected function twins(Dataset $dataset, File $file, EntityManager $em): Array
    {
        echo('looking at: ' . $file->getId() . "\n");
        $hashMatches = array();
        $originalFile = $file->getPhysicalFilePath();
        if (substr($originalFile,0,1) != '/') { $originalFile = "/san/data/store/$originalFile"; }
        if (file_exists($originalFile)) {
            $hash = hash_file("sha256", $originalFile);
            // find other files in fileset to find match, but only matches in in live dataset submission's fileset.
            $dql = "SELECT f FROM \App\Entity\File f
                where f.fileset = :fileset";

            $query = $em->createQuery($dql);
            $query->setParameter('fileset', $dataset->getDatasetSubmission()->getFileset());

            $matches = $query->getResult();
            echo("Looking for matches in " . count($matches) . " total files in fileset.\n");
            foreach ($matches as $match) {
                $matchesPath = $match->getPhysicalFilePath();
                if (substr($matchesPath,0,1) != '/') { $matchesPath = "/san/data/store/$matchesPath"; }
                if (file_exists($matchesPath)) {
                    $hash2 = $this->smartHash($matchesPath);
                    if ($hash == $hash2) {
                        $hashesMatches[] = $match;
                    }
                } else {
                    echo("A file in active fileset not found on disk: " . $matchesPath . "\n");
                }
            }
        } else {
            echo("Original file not found on disk! " . $originalFile . "\n");
        }
        return $hashMatches;
    }

    protected function smartHash($filename)
    {
        $hash = $this->memcached->get($filename);
        if ($hash) {
            return $hash;
        } else {
            $hash = hash_file("sha256", $filename);
            $this->memcached->set($filename, $hash);
        }
    }
}
