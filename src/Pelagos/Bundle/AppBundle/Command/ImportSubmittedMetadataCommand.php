<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Util\DataStore;
use Pelagos\Util\ISOMetadataExtractorUtil;

/**
 * Attempt to import submitted metadata into Dataset Submission.
 *
 * @see ContainerAwareCommand
 */
class ImportSubmittedMetadataCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-submission:import-submitted-metadata')
            ->setDescription('Attempt to import submitted metadata into Dataset Submission.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $query = $entityManager->createQuery(
            'SELECT d FROM ' . Dataset::class . ' d WHERE d.datasetSubmission IS NOT NULL ORDER BY d.udi ASC'
        );
        $datasets = $query->getResult();

        $counts = array(
            'processed' => 0,
            'foundmd' => 0,
            'imported' => 0,
            'invalid' => 0,
            'incompleted' => 0,
        );

        foreach ($datasets as $dataset) {
            $datasetSubmission = $dataset->getDatasetSubmission();
            $counts['processed']++;
            $udi = $dataset->getUdi();
            $output->writeln("Processing submission for $udi (" . $counts['processed'] . '/' . count($datasets) . ').');

            $dataStore = $this->getContainer()->get('pelagos.util.data_store');

            try {
                $metadataFileInfo = $dataStore->getFileInfo($udi, DataStore::METADATA_FILE_TYPE);
            } catch (FileNotFoundException $e) {
                $output->writeln("  No submitted metadata file found for $udi.");
                continue;
            }
            $output->writeln("  Found metadata file for $udi.");
            $counts['foundmd']++;

            $xml = simplexml_load_file(
                $metadataFileInfo->getRealPath(),
                'SimpleXMLElement',
                (LIBXML_NOERROR | LIBXML_NOWARNING)
            );

            if ($xml instanceof \SimpleXMLElement and 'MI_Metadata' == $xml->getName()) {
                $datasetSubmission->getDatasetContacts()->clear();
                $datasetSubmission->getMetadataContacts()->clear();
                ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                    $xml,
                    $datasetSubmission,
                    $entityManager
                );
                // Copy creator and modifier from submission for imported dataset contacts.
                foreach ($datasetSubmission->getDatasetContacts() as $datasetContact) {
                    $datasetContact->setCreator($datasetSubmission->getCreator());
                    $datasetContact->setModifier($datasetSubmission->getModifier());
                }
                // Copy creator and modifier from submission for imported metadata contacts.
                foreach ($datasetSubmission->getMetadataContacts() as $metadataContact) {
                    $metadataContact->setCreator($datasetSubmission->getCreator());
                    $metadataContact->setModifier($datasetSubmission->getModifier());
                }
                $output->writeln("  Imported submitted metadata for $udi.");
                $counts['imported']++;
            } else {
                $output->writeln("  Failed to imported submitted metadata for $udi.");
            }

            $errors = $this->getContainer()->get('validator')->validate($datasetSubmission);

            if (count($errors) > 0) {
                $counts['invalid']++;
                $output->writeln("  Dataset submission for $udi did not validate:");
                foreach ($errors as $error) {
                    $output->writeln('    ' . $error->getMessage());
                }
                // If we don't have accepted metadata.
                if ($dataset->getMetadataStatus() !== DatasetSubmission::METADATA_STATUS_ACCEPTED) {
                    // Change status to incomplete.
                    $datasetSubmissionReflection = new \ReflectionClass($datasetSubmission);
                    $statusReflection = $datasetSubmissionReflection->getProperty('status');
                    $statusReflection->setAccessible(true);
                    $statusReflection->setValue($datasetSubmission, DatasetSubmission::STATUS_INCOMPLETE);
                    $submissionTimeStampReflection = $datasetSubmissionReflection->getProperty('submissionTimeStamp');
                    $submissionTimeStampReflection->setAccessible(true);
                    $submissionTimeStampReflection->setValue($datasetSubmission, null);
                    $output->writeln("  Marked dataset submission for $udi as incomplete.");
                    $counts['incompleted']++;
                }
            }
            $dataset->setDatasetSubmission($datasetSubmission);

            $entityManager->persist($datasetSubmission);
        }

        $entityManager->flush();

        $output->writeln('');
        $output->writeln('Datasets Processed: ' . $counts['processed']);
        $output->writeln('Metadata Found: ' . $counts['foundmd']);
        $output->writeln('Metadata Imported: ' . $counts['imported']);
        $output->writeln('Invalid Submissions: ' . $counts['invalid']);
        $output->writeln('Submissions Incompleted: ' . $counts['incompleted']);

        return 0;
    }
}
