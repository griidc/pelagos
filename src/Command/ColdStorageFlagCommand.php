<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\Person;
use App\Util\RabbitPublisher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command marks a dataset as cold-stored.
 *
 * This command saves the original hash and size, replaces the datafile with a
 * supplied manifest file, then re-triggers filer/hasher in an expected way for
 * a replaced datafile.
 */
class ColdStorageFlagCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:dataset-flag-coldstorage';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Utility Rabbitmq producer instance.
     *
     * @var RabbitPublisher $publisher
     */
    protected $publisher;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param RabbitPublisher        $publisher     A Rabbitmq producer instance.
     */
    public function __construct(EntityManagerInterface $entityManager, RabbitPublisher $publisher)
    {
        $this->entityManager = $entityManager;
        $this->publisher = $publisher;
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
            ->setDescription('Marks dataset in input file is cold-stored and updates datafile')
            ->addArgument('infofile', InputArgument::REQUIRED, 'Filename of the coldinfo-formatted textfile required.');
    }

    /**
     * Symfony command execution section.

     * @param InputInterface  $input  Command args.
     * @param OutputInterface $output Output txt.
     *
     * @throws \Exception If cannot find dataset with provided UDI.
     * @throws \Exception If cannot find dataset submission in dataset.
     * @throws \Exception If stubfile not readable/accessible.
     * @throws \Exception If infofile not readable/accessible.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $systemPerson = $this->entityManager->find(Person::class, 0);

        $infoFileName = $input->getArgument('infofile');
        if (file_exists($infoFileName)) {
            $infoFileContents = file($infoFileName);
            $udi = trim($infoFileContents[0]);

            $nudi = preg_replace('/:/', '.', $udi);
            $infoPath = pathinfo($infoFileName)['dirname'];
            $size = preg_replace('/size \(bytes\): /', '', trim($infoFileContents[1]));
            $hash = preg_replace('/orig sha256: /', '', trim($infoFileContents[2]));
            $originalFilename = preg_replace('/original filename: /', '', trim($infoFileContents[3]));
            $stubFileName = "$infoPath/$nudi-manifest.zip";

            if (file_exists($stubFileName)) {
                $output->writeln("UDI: ($udi)");
                $output->writeln("Original Size: ($size)");
                $output->writeln("Original Hash: ($hash)");
                $output->writeln("Original Filename: ($originalFilename)");
                $output->writeln("stubFileName: ($stubFileName)");
                $output->writeln("Attempting to flag $udi as Cold Stored.");

                $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(array('udi' => $udi));

                if (count($datasets) == 0) {
                    throw new \Exception('Could not find a dataset with the udi provided.');
                } else {
                    $output->writeln('Dataset Found.');
                }
                $dataset = $datasets[0];

                $datasetSubmission = $dataset->getDatasetSubmission();
                if (!($datasetSubmission instanceof DatasetSubmission)) {
                    throw new \Exception('Could not find Dataset Submission.');
                } else {
                    $output->writeln('Submission Found.');

                    // Set Modifier
                    $datasetSubmission->setModifier($systemPerson);

                    // Set filesize, hash, and original filename. (Cold Storage attributes).
                    $datasetSubmission->setDatasetFileColdStorageAttributes($size, $hash, $originalFilename);

                    // Set options for a new replacement datafile of the supplied Cold-Storage stubfile.
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $datasetSubmission->setDatasetFileName(null);
                    $datasetSubmission->setDatasetFileSha256Hash(null);
                    $datasetSubmission->setDatasetFileTransferType(DatasetSubmission::TRANSFER_TYPE_SFTP);
                    $datasetSubmission->setDatasetFileUri($stubFileName);

                    $this->entityManager->persist($datasetSubmission);
                    $this->entityManager->flush($datasetSubmission);

                    //Use rabbitmq to process dataset file and persist the file details. This will
                    //Trigger filer and hasher (via filer) to complete the process.
                    $this->publisher->publish(
                        $dataset->getDatasetSubmission()->getId(),
                        RabbitPublisher::DATASET_SUBMISSION_PRODUCER,
                        'dataset.' . DatasetSubmission::TRANSFER_TYPE_SFTP
                    );
                }
            } else {
                throw new \Exception("Could not open $stubFileName, expected to be at same location as $infoFileName.");
            }
        } else {
            throw new \Exception("Error: Could not open $infoFileName.");
        }
    }
}
