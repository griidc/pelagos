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
use App\Entity\Fileset;
use App\Entity\File;

/**
 * This command gets details about a cold-stored dataset from the stored manifest file.
 *
 */
class GetColdStorageDetailsCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:get-coldstorage-details';

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
            ->setDescription('Marks specified dataset as cold-stored and updates fileset with provided files')
            ->addOption('udi', null, InputOption::VALUE_REQUIRED, 'UDI of dataset to flag as cold stored')
            ;
    }

    /**
     * Symfony command execution section.

     * @param InputInterface  $input  Command args.
     * @param OutputInterface $output Output txt.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getOption('udi');
        $newUdi = preg_replace('/:/', '.', $udi);

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(array('udi' => $udi));
        if (!($dataset instanceof Dataset)) {
            $io->error('Could not find a dataset with UDI ' . $udi);
            return 1;
        }

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!($datasetSubmission instanceof DatasetSubmission)) {
            $io->error('Could not find Dataset Submission in dataset ' . $udi);
            return 1;
        }

        $fileset = $datasetSubmission->getFileset();
        if (!($fileset instanceof Fileset)) {
            $io->error('Could not find Fileset in Dataset Submission for dataset ' . $udi);
            return 1;
        }

        $files = $fileset->getAllFiles();
        $physicalFilePath='';
        foreach ($files as $file) {
            if ($file->getFilePathName() === "$newUdi.hashes.txt") {
                $total=$this->getSummary($file->getPhysicalFilePath());
            }
        }

        $fmt=system("echo $total | numfmt --to=si");
        $io->note("$udi cold-stored as $total bytes ($fmt)");
        $io->success('Done!');

        return 0;
    }

    /**
     * GetSummary Private method that uses a system call to determine the total aggregate filesize for cold-stored datasets based on the stored hashfile.
     *
     * @param String $file
     */
    protected function getSummary($file): int
    {
        $bytes = system("cat /san/data/store/$file | grep -v '^%%' | grep -v '^##' | cut -f1 -d',' | paste -sd+ | bc");
        return $bytes;
    }
}
