<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;

/**
 * This command publishes a rabbit message for every accepted dataset forcing update of DOI info.
 *
 * @see ContainerAwareCommand
 */
class RabbitUpdateAllAcceptedDatasetDOICommand extends ContainerAwareCommand
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
            ->setName('dataset-doi:force-doi-update-all')
            ->setDescription('Force DOI update for all datasets having an accepted submission.');
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
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $datasets = $entityManager->getRepository('Pelagos\Entity\Dataset')->findBy(array(
            'identifiedStatus' => DIF::STATUS_APPROVED));

        $thumper = $this->getContainer()->get('old_sound_rabbit_mq.doi_issue_producer');
        foreach ($datasets as $dataset) {
            $thumper->publish($dataset->getId(), 'update');
            echo 'Requesting DOI update for dataset ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n";
        }

        return 0;
    }
}
