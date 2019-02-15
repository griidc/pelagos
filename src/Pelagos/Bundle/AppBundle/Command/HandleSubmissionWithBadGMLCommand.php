<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\DataCenter;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DistributionPoint;
use Pelagos\Entity\Person;

/**
 * Back fill all the submitted metadata xml to dataset submission.
 *
 * @see ContainerAwareCommand
 */
class HandleSubmissionWithBadGMLCommand extends ContainerAwareCommand
{
    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('onetime:handle-bad-gml-submission')
            ->setDescription('Make the submission accessible in Dataset Review and Submission tool.')
            ->addArgument('udi', InputArgument::REQUIRED, 'What is the UDI of the dataset?');
    }

    /**
     * Script to generate dataset-submissions for already accepted metadata.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception When dataset not found.
     * @throws \Exception When datasetSubmission not found.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $udi = $input->getArgument('udi');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $datasets = $entityManager->getRepository('Pelagos\Entity\Dataset')->findBy(array('udi' => $udi));

        if (count($datasets) == 0) {
            throw new \Exception('Could not find a dataset with the udi provided.');
        }

        $dataset = $datasets[0];

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!($datasetSubmission instanceof DatasetSubmission)) {
            throw new \Exception('Could not find Dataset Submission.');
        }

        $datasetSubmission->setSpatialExtent(null);

        $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_NONE);

//        //clear distribution points if there is one that has no Data Center
        if (count($datasetSubmission->getDistributionPoints()) === 1 ) {
            if (null === $datasetSubmission->getDistributionPoints()[0]->getDataCenter()) {
                $datasetSubmission->removeDistributionPoint($datasetSubmission->getDistributionPoints()[0]);
            }
        }

        $entityManager->persist($datasetSubmission);
        $entityManager->flush();

        $output->writeln('Success: submission ID:' . $datasetSubmission->getId() . ' - Dataset udi: ' . $udi);


        return 0;
    }
}
