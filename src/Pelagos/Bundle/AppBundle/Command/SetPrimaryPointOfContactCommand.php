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
            ->findBy(array('datasetStatus' => Dataset::DATASET_STATUS_ACCEPTED));
        $count = 0;
        foreach ($datasets as $dataset) {
            $datasetSubmission = $dataset->getDatasetSubmission();
            $udi = $dataset->getUdi();
            $output->writeln('Checking primary contact for udi: ' . $udi);

            if ($datasetSubmission instanceof DatasetSubmission) {
                $datasetContacts = $datasetSubmission->getDatasetContacts();
                $primaryContact = array();

                foreach ($datasetContacts as $datasetContact) {
                    if ($datasetContact->isPrimaryContact()) {
                        continue 2;
                    }
                }
                if (empty($primaryContact)) {
                    $firstDatasetContact = $datasetContacts->first();
                    if ($firstDatasetContact instanceof PersonDatasetSubmission) {
                        $firstDatasetContact->setPrimaryContact(true);
                        $output->writeln('Set primary contact for the dataset ' . $udi);
                        $count++;
                    }
                }
                $entityManager->persist($datasetSubmission);
                $entityManager->flush();
            }
        }
        $output->writeln('No. of datasets whose primary contacts have been updated: ' . $count);
    }
}
