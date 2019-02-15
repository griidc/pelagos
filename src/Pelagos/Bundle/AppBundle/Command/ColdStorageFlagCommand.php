<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Person;

class ColdStorageFlagCommand extends ContainerAwareCommand
{

    protected $entityManager;

    protected function configure()
    {
        $this
            ->setName('dataset:flag-coldstorage')
            ->setDescription('Marks dataset in input file is cold-stored and updates datafile')
            ->addArgument('infofile', InputArgument::REQUIRED, 'Filename of the coldinfo-formatted textfile required.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $systemPerson = $entityManager->find(Person::class, 0);

        $infoFileName = $input->getArgument('infofile');
        if(file_exists($infoFileName)){
            $infoFileContents=file($infoFileName);
            $udi = trim($infoFileContents[0]);

            $nudi = preg_replace('/:/', '.', $udi);
            $infoPath = pathinfo($infoFileName)['dirname'];
            $size = preg_replace('/size \(bytes\): /','', trim($infoFileContents[1]));
            $hash = preg_replace('/orig sha256: /','', trim($infoFileContents[2]));
            $stubFileName = "$infoPath/$nudi-manifest.zip";

            if(file_exists($stubFileName)){
                $output->writeln("UDI: ($udi)");
                $output->writeln("Size: ($size)");
                $output->writeln("Hash: ($hash)");
                $output->writeln("stubFileName: ($stubFileName)");

                $output->writeln("Attempting to flag $udi as Cold Stored.");

                $datasets = $entityManager->getRepository('Pelagos\Entity\Dataset')->findBy(array('udi' => $udi));

                if (count($datasets) == 0) {
                    throw new \Exception('Could not find a dataset with the udi provided.');
                } else {
                    $output->writeln("Dataset Found.");
                }

                $dataset = $datasets[0];

                $datasetSubmission = $dataset->getDatasetSubmission();
                if (!($datasetSubmission instanceof DatasetSubmission)) {
                    throw new \Exception('Could not find Dataset Submission.');
                } else {
                    $output->writeln("Submission Found.");
                    // Create a new submission using latest via constructor.
                    $newDatasetSubmission = new DatasetSubmission($datasetSubmission);
                    // Set filesize of original file in new submission.
                    $newDatasetSubmission->setDatasetFileColdStorageArchiveSize($size);
                    // Set hash of original file in new submission.
                    $newDatasetSubmission->setDatasetFileColdStorageArchiveSha256Hash($hash);

                    // Persist into new dataset. This would have been a one line call
                    // to entityHandler, if we'd of been able to use it, but not usable in
                    // commands.
                    if ($entityManager->contains($newDatasetSubmission)) {
                        throw new \Exception('Attempted to create a dataset submission that is already tracked');
                    }
                    $newDatasetSubmission->setCreator($systemPerson);
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
                    //$this->entityEventDispatcher->dispatch($entity, $entityEventName);

                    // Trigger filer against stubfile.
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
