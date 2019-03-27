<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\PersonDatasetSubmission;

/**
 * Set Primary Point of Contact for Accepted Datasets.
 *
 * @see ContainerAwareCommand
 */
class SetPrimaryPointOfContactCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset:set-primary-point-of-contact')
            ->setDescription('Set primary point of contact for datasets which do not have a primary');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $datasets = $entityManager->getRepository(Dataset::class)
            ->findBy(array('datasetSubmissionStatus' => [DatasetSubmission::STATUS_COMPLETE, DatasetSubmission::STATUS_IN_REVIEW]));
        $count = 0;
        foreach ($datasets as $dataset) {
            $udi = $dataset->getUdi();
            $output->writeln('Checking primary contact for udi: ' . $udi);

            // Using this getter so that irrespective of the dataset status, it would get the right Dataset Submission
            $datasetSubmissionLatest = $dataset->getLatestDatasetReview();
            $datasetSubmission = $dataset->getDatasetSubmission();
            //To fix edge case scenarios, where drafts are present
            if ($datasetSubmission !== $datasetSubmissionLatest) {
                $this->setPrimary($datasetSubmission, $output);
            }
            $this->setPrimary($datasetSubmissionLatest, $output);
            $count++;
            $entityManager->persist($dataset);
            $entityManager->flush();
        }
        $output->writeln('No. of datasets whose primary contacts have been updated: ' . $count);
    }

    /**
     * Sets primary point of contact for dataset submission if not present.
     *
     * @param DatasetSubmission $datasetSubmission An instance of Dataset Submission.
     * @param OutputInterface   $output            An OutputInterface instance.
     *
     * @return void
     */
    private function setPrimary(DatasetSubmission $datasetSubmission, OutputInterface $output)
    {
        if ($datasetSubmission instanceof DatasetSubmission) {
            $datasetContacts = $datasetSubmission->getDatasetContacts();
            $primaryContact = array();

            foreach ($datasetContacts as $datasetContact) {
                if ($datasetContact->isPrimaryContact()) {
                    continue 1;
                }
            }
            if (empty($primaryContact)) {
                $firstDatasetContact = $datasetContacts->first();
                if ($firstDatasetContact instanceof PersonDatasetSubmission) {
                    $firstDatasetContact->setPrimaryContact(true);
                    $output->writeln('Set primary contact for the dataset ' .
                        $datasetSubmission->getDataset()->getUdi());
                }
            }
        }
    }
}
