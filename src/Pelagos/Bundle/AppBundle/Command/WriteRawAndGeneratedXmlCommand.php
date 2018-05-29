<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;

/**
 * Write both the raw XML metadata and an entity-source generated XML file for every dataset.
 *
 * @see ContainerAwareCommand
 */
class WriteRawAndGeneratedXmlCommand extends ContainerAwareCommand
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
            ->setName('dataset:write-metadata-files')
            ->setDescription('Write both the raw XML metadata and an entity-source generated XML file for every accepted data.')
            ->addArgument('UDI', InputArgument::OPTIONAL, 'UDI of single dataset to write.');
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

        $udi = $input->getArgument('UDI');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if ($udi) {
            $datasets = $em->getRepository('Pelagos\Entity\Dataset')->findBy(array('udi' => $udi, 'metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED, 'availabilityStatus' !=> DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED));
            $output->writeln("Handling single retrieval for user-provided udi of an accepted dataset: $udi.");
        } else {
            $datasets = $em->getRepository('Pelagos\Entity\Dataset')->findBy(array('metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED, 'availabilityStatus' !=> DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED));
            $output->writeln('Processing all ' . count($datasets) . ' accepted datasets.');
        }


        foreach ($datasets as $dataset) {
            $udi = $dataset->getUdi();
            $output->writeln("Processing $udi.");
            $metadata = $dataset->getMetadata();
            #$output->writeln("Writing historical XML for $udi as $udi-raw.xml");
            #if ($metadata instanceof Metadata) {
            #    // Write XML from Metadata Entity
            #} else {
            #    // write empty file
            #}

            // Write XML from Generator (Entity sourced).
            $output->writeln("Writing Generated XML for $udi as $udi-generated.xml");
            $boundingBoxArray = $this->getBoundingBox($dataset);
            $xml = $this->getContainer()->get('pelagos.util.metadata')->getXmlRepresentation($dataset, $boundingBoxArray);
            $output->writeln($xml);

        }

        $this->output = $output;

        return 0;
    }

    /**
     * Get the bounding box for the dataset.
     *
     * @param Dataset $dataset The dataset for which the bounding box is generated.
     *
     * @return array
     */
    private function getBoundingBox(Dataset $dataset)
    {
        $geoUtil = $this->getContainer()->get('pelagos.util.geometry');
        $gml = $dataset->getDatasetSubmission()->getSpatialExtent();
        $boundingBoxArray = array();
        if ($gml) {
            try {
                $boundingBoxArray = $geoUtil->calculateGeographicBoundsFromGml($gml);
            } catch (InvalidGmlException $e) {
                $errors[] = $e->getMessage() . ' while attempting to calculate bonding box from gml';
                $boundingBoxArray = array();
            }
        }
        return $boundingBoxArray;
    }


}
