<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\FileSet;
use App\Message\DatasetSubmissionFiler;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

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
     * The message bus for dispatching the filer message.
     *
     * @var MessageBusInterface $messageBus
     */
    protected $messageBus;

    /**
     * This contains a list of submission IDs that need to be queued to filer.
     *
     * @var Array $submissionsListForFiler
     */
    protected $submissionsListForFiler;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        $this->submissionsListForFiler = array();
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    /**
     * Configure function to allow for options and parameters.
     */
    protected function configure()
    {
        $this
            ->setDescription('This command will migrate the datastore to the new datastore.')
            ->addOption('qf', null, InputOption::VALUE_NONE, 'Queue Dataset to Filer')
            ->addOption('ifc', null, InputOption::VALUE_NONE, 'Do not check if dataset file exist on disk.')
        ;
    }

    /**
     * The symfony command execute function.
     *
     * @param InputInterface  $input  The Symfony Console Input.
     * @param OutputInterface $output The Symfony Console Output.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('This is is dataset Migration Tool');

        $queueFiler = $input->getOption('qf') ? true : false;
        $checkFileExist = $input->getOption('ifc') ? true : false;

        $datasetRepository = $this->entityManager->getRepository(Dataset::class);

        $datasets = $datasetRepository->findAll();

        $io->section('Migrating Datasets Files');

        $dataStore = $io->ask('Path to data store:', '/san/data/store');

        $progressBar = new ProgressBar($output, count($datasets));
        $progressBar->start();

        foreach ($datasets as $dataset) {
            $udi = $dataset->getUdi();

            $status = $dataset->getDatasetStatus();

            //$io->note(sprintf('UDI = %s.', $udi));

            if ($status === Dataset::DATASET_STATUS_IN_REVIEW) {
                $datasetSubmission = $dataset->getLatestDatasetReview();
                $this->setFile($datasetSubmission, $dataStore, $queueFiler);
                $datasetSubmission = $dataset->getDatasetSubmission();
                $this->setFile($datasetSubmission, $dataStore, $queueFiler);
            } else {
                $datasetSubmission = $dataset->getDatasetSubmission();
                $this->setFile($datasetSubmission, $dataStore, $queueFiler);
            }

            $this->entityManager->persist($dataset);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(3);

        $io->section('Flushing');
        $this->entityManager->flush();

        $io->section('Queuing Filer');
        $this->submissionsListForFiler = array_unique($this->submissionsListForFiler);
        $io->note(sprintf('Files For Queue %d.', count(($this->submissionsListForFiler))));

        foreach ($this->submissionsListForFiler as $datasetSubmissionId) {
            $datasetSubmissionFilerMessage = new DatasetSubmissionFiler($datasetSubmissionId);
            $this->messageBus->dispatch($datasetSubmissionFilerMessage);
        }

        $io->success('Done!');

        return 0;
    }

    /**
     * Set file data for the dataset submission.
     *
     * @param DatasetSubmission  $datasetSubmission The Dataset Submission to process.
     * @param String             $dataStore         The path to the datatore.
     * @param Boolean            $queueFiler        Should this also be queued to the filer.
     */
    protected function setFile(
        DatasetSubmission $datasetSubmission = null,
        String $dataStore,
        bool $queueFiler
    ) {
        if ($datasetSubmission instanceof DatasetSubmission) {
            $udi = $datasetSubmission->getDataset()->getUdi();

            $fileName = "$dataStore/$udi/$udi.dat";

            if (!file_exists($fileName)) {
                return;
            }

            $fileData = $this->getFileData($datasetSubmission);

            $file = new File();
            $file->setFilePathName($fileData['datasetFileName']);
            $file->setFileSize($fileData['datasetFileSize']);
            $file->setFileSha256Hash($fileData['datasetFileSha256Hash']);

            $file->setUploadedAt($datasetSubmission->getModificationTimeStamp());
            $file->setUploadedBy($datasetSubmission->getModifier());

            $file->setDescription('Original Dataset File');

            $file->setPhysicalFilePath($fileName);
            $file->setStatus($queueFiler ? File::FILE_NEW : File::FILE_DONE);

            $fileset = $datasetSubmission->getFileset();

            $newFileset = new Fileset();
            $newFileset->addFile($file);
            $datasetSubmission->setFileset($newFileset);

            if ($queueFiler === true) {
                $this->submissionsListForFiler[] = $datasetSubmission->getId();
            }
        }
    }

    /**
     * Get the file data from the submission, because the settings have been forwarded.
     *
     * @param DatasetSubmission $datasetSubmission The Dataset Submission to process.
     */
    protected function getFileData($datasetSubmission)
    {
        $datasetSubmissionid = $datasetSubmission->getid();

        $qb = $this->entityManager
            ->getRepository(DatasetSubmission::class)
            ->createQueryBuilder('datasetSubmission');

        $qb
            ->select('datasetSubmission.datasetFileName, datasetSubmission.datasetFileSize, datasetSubmission.datasetFileSha256Hash')
            ->where('datasetSubmission.id = (:datasetSubmissionid)')
            ->setParameter('datasetSubmissionid', $datasetSubmissionid);

        return $qb->getQuery()->getResult()[0];
    }
}
