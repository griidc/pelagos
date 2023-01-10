<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PelagosVerifyFilesetCommand extends Command
{
    protected static $defaultName = 'pelagos:verify-fileset';
    protected static $defaultDescription = 'Checks all files exist for a specified UDI.';

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
     * @param Datastore              $datastore     Datastore utility instance.
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
            ->addArgument('udi', InputArgument::REQUIRED, 'The UDI')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('udi');

        /** @var Dataset $dataset */
        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(
            array('udi' => $udi)
        );

        #$datasetSubmission = $dataset->getLatestDatasetReview();
        $datasetSubmission = $dataset->getDatasetSubmission();

        //$files = $datasetSubmission->getFileset()->getAllFiles()->filter(function (File $file) {
        //    return $file->getStatus() === File::FILE_DONE;
        //});

        $files = $datasetSubmission->getFileset()->getAllFiles();

        $progressBar = new ProgressBar($output, count($files));;
        /** @var File $file */
        foreach ($files as $file) {
            $prefix = '/san/data/store';
            $physicalFilePath = $file->getPhysicalFilePath();
            if (preg_match('/^\/san\/home/', $physicalFilePath)) {
                $filePath = $physicalFilePath;
            } else {
                $filePath = $prefix . '/' . $physicalFilePath;
            }

            if (is_readable($filePath)) {
                $io->note("OK: $filePath");
            } else {
                $io->error("MISSING: $filePath");
            }
        }

        $io->success('Done!');

        return 0;
    }
}
