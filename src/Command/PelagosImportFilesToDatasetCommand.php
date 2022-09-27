<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use App\Entity\Person;
use App\Message\DatasetSubmissionFiler;
use App\Message\DeleteFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class PelagosImportFilesToDatasetCommand extends Command
{
    protected static $defaultName = 'pelagos:import-files-to-dataset';
    protected static $defaultDescription = 'Imports file to a dataset';

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
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('basePath', InputArgument::REQUIRED, 'Path to files')
            ->addArgument('udi', InputArgument::REQUIRED, 'The UDI')
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
        $basePath = $input->getArgument('basePath');
        $udi = $input->getArgument('udi');

        $io->note("Starting import for UDI:$udi");

        $systemPerson = $this->entityManager->find(Person::class, 0);

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
        if (!$dataset instanceof Dataset) {
            $io->error("Dataset not found for $udi");
            return 1;
        }

        $datasetSubmission = $dataset->getLatestDatasetReview();
        if (!$datasetSubmission instanceof DatasetSubmission) {
            $io->error("Dataset Submission not found for $udi");
            return 1;
        }

        $files = $this->dirToList($basePath);

        $fileset = $datasetSubmission->getFileset();

        $deleteFile = null;

        if (!$fileset instanceof Fileset) {
            throw new \Exception('The dataset does not have a fileset, QUITTING!');
            return 1;
        } else {
            $deleteFile = $fileset->getProcessedFiles()->first();
            if ($deleteFile instanceof File) {
                $physicalFilePath = $deleteFile->getPhysicalFilePath();
                $pattern = '/^\w\w\.x\d{3}\.\d{3}\:\d{4}\/\w\w\.x\d{3}\.\d{3}\:\d{4}\.dat$/';
                if (preg_match($pattern, $physicalFilePath) === 1) {
                    $deleteFileMessage = new DeleteFile($physicalFilePath);
                    $this->messageBus->dispatch($deleteFileMessage);
                    $fileset->removeFile($deleteFile);
                } else {
                    throw new \Exception('Original .dat file can not be located in the fileset, QUITTING!');
                    return 1;
                }
            }
        }

        foreach ($files as $file) {
            $fileName = $file;
            $filePath = $basePath . DIRECTORY_SEPARATOR . $file;
            $fileSize = filesize($filePath);

            if ($fileset->doesFileExist($fileName)) {
                throw new \Exception("File: $fileName already imported, QUITTING!");
                return 1;
            }

            $newFile = new File();
            $newFile->setFilePathName(trim($fileName));
            $newFile->setFileSize($fileSize);
            $newFile->setUploadedAt(new \DateTime('now'));
            $newFile->setUploadedBy($systemPerson);
            $newFile->setPhysicalFilePath($filePath);
            $newFile->setDescription('Imported by unpack tool.');
            $newFile->setStatus(File::FILE_IN_QUEUE);
            $fileset->addFile($newFile);
            $this->entityManager->persist($newFile);
        }

        $this->entityManager->flush();

        $datasetSubmissionFilerMessage = new DatasetSubmissionFiler($datasetSubmission->getId());
        $this->messageBus->dispatch($datasetSubmissionFilerMessage);

        $io->success(sprintf('Files imported for %s.', $udi));

        return 0;
    }

    /**
     * This function will take a basepath, and create a list of files and their path.
     *
     * @param string $basePath  The base path where the files are located.
     * @param string $directory A sub directory to look into.
     * @param array  $files     The list of files to add to.
     *
     * @return array The list of files paths in an array.
     */
    private function dirToList(string $basePath, string $directory = '', array $files = array()): array
    {
        $filePath = $basePath . DIRECTORY_SEPARATOR . $directory;

        $contents = scandir($filePath);

        foreach ($contents as $item) {
            if (!in_array($item, array(".",".."))) {
                if (is_dir($filePath . DIRECTORY_SEPARATOR . $item)) {
                    $files = $this->dirToList($basePath, $directory . DIRECTORY_SEPARATOR .  $item, $files);
                } else {
                    $files[] = ltrim($directory . DIRECTORY_SEPARATOR . $item, DIRECTORY_SEPARATOR);
                }
            }
        }

        return $files;
    }
}
