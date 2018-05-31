<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

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
            ->setDescription('Write both the raw XML metadata and an entity-source generated XML file '
            . 'for every accepted data.')
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
            $datasets = $em->getRepository('Pelagos\Entity\Dataset')->findBy(
                array(
                    'udi' => $udi,
                    'metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED
                )
            );
            $output->writeln("Handling single retrieval for user-provided udi of an accepted dataset: $udi.");
        } else {
            $datasets = $em->getRepository('Pelagos\Entity\Dataset')->findBy(
                array(
                    'metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED
                )
            );
            $output->writeln('Processing all ' . count($datasets) . ' accepted datasets.');
        }


        foreach ($datasets as $dataset) {
            // Check to see if dataset has a distribution contact, otherwise don't attempt.
            if (count($dataset->getDatasetSubmission()->getDistributionPoints()) > 0) {
                $udi = $dataset->getUdi();
                $outdir = $_SERVER['HOME'] . '/output';
                $newXMLOutputFile = "$udi.generated.xml";
                $oldXMLOutputFile = "$udi.historical.xml";
                $newXMLOutput = new StreamOutput(fopen("$outdir/$newXMLOutputFile", 'w'));
                $oldXMLOutput = new StreamOutput(fopen("$outdir/$oldXMLOutputFile", 'w'));

                $output->writeln("Processing $udi.");

                // Write XML from Generator (Entity sourced).
                $output->writeln("Writing Generated XML for $udi as: $outdir/$newXMLOutputFile.");
                $boundingBoxArray = $this->getBoundingBox($dataset);
                $xml = $this->getContainer()->get('pelagos.util.metadata')->getXmlRepresentation(
                    $dataset,
                    $boundingBoxArray
                );
                $newXMLOutput->writeln($xml);

                // Write historical XML from Metadata Entity.
                $output->writeln("Writing historical XML for $udi as $outdir/$oldXMLOutputFile");
                $metadata = $dataset->getMetadata();
                if ($metadata instanceof Metadata) {
                    $oldXMLOutput->writeln($metadata->getXml()->asXML());
                }

            } else {
                $output->writeln("$udi missing distribution point. Skipping.");
            }
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
