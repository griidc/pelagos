<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DOI;
use Pelagos\Util\DOIutil;

/**
 * This command publishes a rabbit message for every accepted dataset forcing update of DOI info.
 *
 * @see ContainerAwareCommand
 */
class RabbitPublishAllApprovedNonRestrictedCommand extends ContainerAwareCommand
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
            ->setName('dataset-doi:pub-all-appr-nonres-datasets')
            ->setDescription('Make DOI public for all approved, non-restricted datasets.');
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
            'metadataStatus' => array(
                Dataset::DATASET_STATUS_ACCEPTED,
            )
        ));

        $thumper = $this->getContainer()->get('old_sound_rabbit_mq.doi_issue_producer');
        foreach ($datasets as $dataset) {
            $doiUtil = new DOIutil;
            try {
                $doiMetadata = $doiUtil->getDOIMetadata($dataset->getDoi());
            } catch (\Exception $e) {
                 $output->writeln('Did not get a response from EzID for ' . $dataset->getUdi());
            }

            if (array_key_exists('_status', $doiMetadata) and $doiMetadata['_status'] === 'public') {
                echo 'Skipping because EzID already reports dataset ' . $dataset->getId() . ' (' . $dataset->getUdi() . ") is public.\n";
            } else {
                $thumper->publish($dataset->getId(), 'publish');
                echo 'Requesting DOI publish update for dataset ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n";
            }
        }

        return 0;
    }
}
