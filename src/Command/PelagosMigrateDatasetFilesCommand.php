<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\FileSet;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\PropertyAccess\PropertyAccess;

class PelagosMigrateDatasetFilesCommand extends Command
{
    protected static $defaultName = 'pelagos:migrate-dataset-files';
    
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
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command will migrate the datastore to the new datastore.')
            ->addOption('qf', null, InputOption::VALUE_NONE, 'Queue Dataset to Filer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Migrating Datasets Files');
        
        if ($input->getOption('qf')) {
            $io->text('Also queueing the filer!');
            $queueFiler = true;
        } else {
            $queueFiler = false;
        }
        
        $datasetRepository = $this->entityManager->getRepository(Dataset::class);
        
        $datasets = $datasetRepository->findAll();
        
        foreach ($datasets as $dataset) {
            $udi = $dataset->getUdi();

            $status = $dataset->getDatasetStatus();
            
            $io->note(sprintf('UDI = %s.', $udi));
            
            if ($status === Dataset::DATASET_STATUS_IN_REVIEW) {
                $datasetSubmission = $dataset->getLatestDatasetReview();
                $this->setFile($datasetSubmission);
                $datasetSubmission = $dataset->getDatasetSubmission();
                $this->setFile($datasetSubmission);
            } else {
                $datasetSubmission = $dataset->getDatasetSubmission();
                $this->setFile($datasetSubmission);
            }
            
            $this->entityManager->persist($dataset);
            
        }

        $this->entityManager->flush();
        $io->success('Done!');

        return 0;
    }
    
    function setFile($datasetSubmission)
    {
        
        if ($datasetSubmission instanceof DatasetSubmission) {
            $udi = $datasetSubmission->getDataset()->getUdi();
            
            // TODO: Run querybuild to get filename and hash and size...
            
            $file = new File();
            $file->setFilePathName($datasetSubmission->getDatasetFileName());
            $file->setFileSize($datasetSubmission->getDatasetFileSize());
            $file->setFileSha256Hash($datasetSubmission->getDatasetFileSha256Hash());

            $file->setUploadedAt($datasetSubmission->getModificationTimeStamp());
            $file->setUploadedBy($datasetSubmission->getModifier());

            $file->setDescription('Original Dataset File');

            $file->setPhysicalFilePath("/san/data/store/$udi/$udi.dat");
            $file->setStatus(File::FILE_DONE);

            $fileset = $datasetSubmission->getFileset();

            $newFileset = new Fileset();
            $newFileset->addFile($file);
            $datasetSubmission->setFileset($newFileset);
        }
    }
}
