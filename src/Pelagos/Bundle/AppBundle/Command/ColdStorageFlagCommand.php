<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
     * @throws \Exception If stubfile not readable/accessible.
     * @throws \Exception If infofile not readable/accessible.
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
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
                $output->writeln("Original Size: ($size)");
                $output->writeln("Original Hash: ($hash)");
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

                    // Set filesize of original file.
                    $datasetSubmission->setDatasetFileColdStorageArchiveSize($size);
                    // Set hash of original file.
                    $datasetSubmission->setDatasetFileColdStorageArchiveSha256Hash($hash);

                    // Set options for a new replacement datafile of the supplied Cold-Storage stubfile.
                    $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);
                    $datasetSubmission->setDatasetFileName(null);
                    $datasetSubmission->setDatasetFileSize(null);
                    $datasetSubmission->setDatasetFileSha256Hash(null);
                    $datasetSubmission->setDatasetFileTransferType(DatasetSubmission::TRANSFER_TYPE_SFTP);
                    $datasetSubmission->setDatasetFileUri($stubFileName);
                    $datasetSubmission->setDistributionFormatName('Cold Storage archive - ' .
                        $datasetSubmission->getDistributionFormatName());

                    $entityManager->persist($datasetSubmission);
                    $entityManager->flush($datasetSubmission);

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
    }
}
