<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\FileSet;
use App\Message\DatasetSubmissionFiler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Command Class to migrate existing dataset files to new store.
 */
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
    protected $submissionsListForFiler = array();

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param MessageBusInterface    $messageBus    The messenger bus.
     */
    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
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
     *
     * @return int Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('This is is dataset Migration Tool');

        $queueFiler = $input->getOption('qf') ? true : false;
        $ignoreFileExistCheck = $input->getOption('ifc') ? true : false;

        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();

        $io->section('Migrating Datasets Files');

        $dataStore = $io->ask('Path to data store:', '/san/data/store');

        $progressBar = new ProgressBar($output, count($datasets));
        $progressBar->start();

        $listOfDrafts = array();

        foreach ($datasets as $dataset) {
            $udi = $dataset->getUdi();

            $status = $dataset->getDatasetStatus();

            $lastDatasetSubmission = $dataset->getLatestDatasetReview();

            $subState = 0;

            if ($lastDatasetSubmission instanceof DatasetSubmission) {
                $subState = $lastDatasetSubmission->getStatus();
                $fileUri = $lastDatasetSubmission->getDatasetFileUri();
                if ($subState === DatasetSubmission::STATUS_INCOMPLETE and !empty($fileUri)) {
                    $listOfDrafts[] = array('udi' => $udi, 'fileUri' => $fileUri);
                }
            }

            $datasetSubmission = $dataset->getLatestDatasetReview();
            $this->setFile($dataStore, $queueFiler, $ignoreFileExistCheck, $datasetSubmission);
            $datasetSubmission = $dataset->getDatasetSubmission();
            $this->setFile($dataStore, $queueFiler, $ignoreFileExistCheck, $datasetSubmission);

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

        $io->section('Draft Submissions');

        foreach ($listOfDrafts as $draft) {
            $io->note(sprintf('File %s for UDI: %s.', $draft['fileUri'], $draft['udi']));
        }

        $io->success('Done!');

        return 0;
    }

    /**
     * Set file data for the dataset submission.
     *
     * @param String             $dataStore            The path to the datatore.
     * @param bool               $queueFiler           Should this also be queued to the filer.
     * @param bool               $ignoreFileExistCheck If checking for file exist should be ignored.
     * @param DatasetSubmission  $datasetSubmission    The Dataset Submission to process.
     *
     * @return void
     */
    protected function setFile(
        string $dataStore,
        bool $queueFiler,
        bool $ignoreFileExistCheck,
        DatasetSubmission $datasetSubmission = null
    ) {
        if ($datasetSubmission instanceof DatasetSubmission) {
            $udi = $datasetSubmission->getDataset()->getUdi();

            $fileName = "$dataStore/$udi/$udi.dat";

            if (!file_exists($fileName) or !$ignoreFileExistCheck) {
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

            $file->setPhysicalFilePath("$udi/$udi.dat");
            $file->setStatus($queueFiler ? File::FILE_NEW : File::FILE_DONE);

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
     *
     * @return array An array of file data fields.
     */
    protected function getFileData(DatasetSubmission $datasetSubmission)
    {
        $datasetSubmissionid = $datasetSubmission->getId();

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
