<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Person;

/**
 * This command marks a dataset as cold-stored.
 *
 * This command saves the original hash and size, replaces the datafile with a
 * supplied manifest file, then re-triggers filer/hasher in an expected way for
 * a replaced datafile.
 */
class ColdStorageFlagCommand extends ContainerAwareCommand
{

    /**
     * Doctrine EntityManager, as provided in container.
     *
     * @var mixed entityManager
     */
    protected $entityManager;

    /**
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset:flag-coldstorage')
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
     * @throws \Exception If new submission already somehow exists.
     * @throws \ReflectionException If reflection class fails.
     * @throws \Exception If stubfile not readable/accessible.
     * @throws \Exception If infofile not readable/accessible.
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entityEventDispatcher = $this->getContainer()->get('pelagos.event.entity_event_dispatcher');
        $systemPerson = $entityManager->find(Person::class, 0);

        $infoFileName = $input->getArgument('infofile');
        if (file_exists($infoFileName)) {
            $infoFileContents = file($infoFileName);
            $udi = trim($infoFileContents[0]);

            $nudi = preg_replace('/:/', '.', $udi);
            $infoPath = pathinfo($infoFileName)['dirname'];
            $size = preg_replace('/size \(bytes\): /', '', trim($infoFileContents[1]));
            $hash = preg_replace('/orig sha256: /', '', trim($infoFileContents[2]));
            $stubFileName = "$infoPath/$nudi-manifest.zip";

            if (file_exists($stubFileName)) {
                $output->writeln("UDI: ($udi)");
                $output->writeln("Size: ($size)");
                $output->writeln("Hash: ($hash)");
                $output->writeln("stubFileName: ($stubFileName)");

                $output->writeln("Attempting to flag $udi as Cold Stored.");

                $datasets = $entityManager->getRepository('Pelagos\Entity\Dataset')->findBy(array('udi' => $udi));

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
                    // Create a new submission using latest via constructor.
                    $newDatasetSubmission = new DatasetSubmission($datasetSubmission);
                    if ($entityManager->contains($newDatasetSubmission)) {
                        throw new \Exception('Attempted to create a dataset submission that is already tracked');
                    }
                    $output->writeln('original submission ID is: ' . $dataset->getDatasetSubmission()->getId());
                    $output->writeln('new submission ID is initially: ' . $newDatasetSubmission->getId());

                    // This method sets submission status to: DatasetSubmission::STATUS_IN_REVIEW (3).
                    $newDatasetSubmission->setDatasetSubmissionReviewStatus();
                    // Set filesize of original file in new submission.
                    $newDatasetSubmission->setDatasetFileColdStorageArchiveSize($size);
                    // Set hash of original file in new submission.
                    $newDatasetSubmission->setDatasetFileColdStorageArchiveSha256Hash($hash);
                    // Set the creator of this entity to the user known as 'system'.
                    $newDatasetSubmission->setCreator($systemPerson);

                    // Set options for a new replacement datafile of the supplied Cold-Storage stubfile.
                    $newDatasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $newDatasetSubmission->setDatasetFileName(null);
                    $newDatasetSubmission->setDatasetFileSize(null);
                    $newDatasetSubmission->setDatasetFileSha256Hash(null);
                    $newDatasetSubmission->setDatasetFileTransferType(DatasetSubmission::TRANSFER_TYPE_SFTP);
                    $newDatasetSubmission->setDatasetFileUri($stubFileName);
                    $newDatasetSubmission->setDistributionFormatName('Cold Storage archive - ' .
                        $datasetSubmission->getDistributionFormatName());

                    // This is normally null, unless manually set. Handle that gracefully.
                    $newDatasetSubmissionId = $newDatasetSubmission->getId();
                    $metadata = $entityManager->getClassMetaData(get_class($newDatasetSubmission));
                    $newDatasetSubmissionIdGenerator = $metadata->idGenerator;
                    if ($newDatasetSubmissionId !== null) {
                        // Temporarily change the ID generator to AssignedGenerator.
                        $metadata->setIdGenerator(new AssignedGenerator());
                    }

                    $entityManager->persist($newDatasetSubmission);
                    $entityManager->flush($newDatasetSubmission);

                    if ($newDatasetSubmissionId !== null) {
                        $metadata->setIdGenerator($newDatasetSubmissionIdGenerator);
                    }

                    $dataset->setDatasetSubmission($newDatasetSubmission);
                    $entityManager->persist($dataset);
                    $entityManager->flush($dataset);

                    // This is an ugly hack, having to re-persist, but there seems not to be a cleaner way...
                    // Reset original status.
                    try {
                        $newDatasetSubmissionReflection = new \ReflectionClass($newDatasetSubmission);
                        $statusReflection = $newDatasetSubmissionReflection->getProperty('status');
                        $statusReflection->setACcessible(true);
                        $statusReflection->setValue($newDatasetSubmission, $datasetSubmission->getStatus());
                    } catch (\ReflectionException $exception) {
                        throw new \ReflectionException('Reflection class failed ' . $exception->getMessage());
                    }
                    $entityManager->persist($newDatasetSubmission);
                    $entityManager->flush($newDatasetSubmission);

                    $output->writeln('Original DatasetSubmission Status: ' . $datasetSubmission->getStatus());
                    $output->writeln('New submission ID is now: ' . $newDatasetSubmission->getId());

                    $output->writeln('New DatasetSubmission Status referenced in dataset: '
                        . $dataset->getDatasetSubmission()->getStatus());
                    $output->writeln('New submission ID in dataset is now: '
                        . $dataset->getDatasetSubmission()->getId());

                    //Use rabbitmq to process dataset file and persist the file details. This will
                    //Trigger filer and hasher (via filer) to complete the process.
                    $this->getContainer()->get('old_sound_rabbit_mq.dataset_submission_producer')->publish(
                        $dataset->getDatasetSubmission()->getId(),
                        'dataset.SFTP'
                    );
                }
            } else {
                throw new \Exception("Could not open $stubFileName, expected to be at same location as $infoFileName.");
            }
        } else {
            throw new \Exception("Error: Could not open $infoFileName.");
        }
        return 0;
    }
}
