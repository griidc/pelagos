<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * Mark dataset's dataset submission as remotely hosted, as identified by hardcoded UDI.
 *
 * @see ContainerAwareCommand
 */
class ManuallyMarkRemotelyHostedCommand extends ContainerAwareCommand
{
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-submission:set-remotely-hosted')
            ->setDescription('Manually set a dataset submission as Remotely Hosted.')
            ->addArgument('UDI', InputArgument::REQUIRED, 'What is the UDI of the dataset submission\'s dataset?');
    }

    /**
     * Executes the current command.
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

        $udi = $input->getArgument('UDI');

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

        $datasetSubmission->setDatasetFileTransferStatus(DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED);
        $entityManager->persist($datasetSubmission);
        $entityManager->flush();
        $output->writeln("The dataset submission for dataset with UDI $udi has now been marked at remotely hosted.");

        // Save output object for use in other methods.
        $this->output = $output;

        return 0;
    }
}
