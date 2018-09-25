<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;

/**
 * Post-migration Script for moving from EZid Library to Datacite.
 *
 * @see ContainerAwareCommand
 */
class DoiDatacitePostMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-doi:post-migration-script')
            ->setDescription('Recreate DOIs for DIF approved datasets.');
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
        //Recreate the DOIs which we deleted.

        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array(
            'datasetStatus' => array(
                DatasetSubmission::METADATA_STATUS_NONE,
            )
        ));

        $rabbitProducer = $this->getContainer()->get('old_sound_rabbit_mq.doi_issue_producer');
        foreach ($datasets as $dataset) {
            // Creating DOI's which are DIF approved datasets.
            if ($dataset->getIdentifiedStatus() === DIF::STATUS_APPROVED) {
                $rabbitProducer->publish($dataset->getId(), 'issue');
                $output->writeln('Attempting to issue DOI for Dataset ' . $dataset->getId());
            }
        }
    }
}
