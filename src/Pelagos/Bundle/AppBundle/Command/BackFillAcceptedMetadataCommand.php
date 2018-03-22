<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Pelagos\Entity\DatasetSubmission;
use Pelagos\Util\ISOMetadataExtractorUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Back fill all the accepted dataset metadata xml to dataset submission.
 *
 * @see ContainerAwareCommand
 */
class BackFillAcceptedMetadataCommand extends ContainerAwareCommand
{
    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-submission:back-fill-accepted-metadata-command')
            ->setDescription('Back fill accepted metadata into dataset submission.');
    }

    /**
     * Script to generate dataset-submissions for already accepted metadata.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception  When dataset or person is not found.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This command takes no input.
        unset($input);

        // to show no. of datasets migrated.
        $i = 0;

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $datasets = $entityManager
            ->getRepository('Pelagos\Entity\Dataset')
            ->findBy(array('metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED));

        foreach ($datasets as $dataset) {

            $metadata = $dataset->getMetadata();
            $submitterId = $metadata->getModifier()->getId();

            $submitterPerson = $entityManager
                ->getRepository('Pelagos\Entity\Person')
                ->findBy(array('id' => 0));

            if (!empty($submitterPerson)) {
                $person = $submitterPerson[0];
            }

            //Create a new one based on the already existing old one.
            $datasetSubmission = new DatasetSubmission($dataset->getDatasetSubmission());

            $this->clearDatasetSubmission($datasetSubmission);

            ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                $metadata->getXml(),
                $datasetSubmission,
                $entityManager
            );

            // Had to set these fields because ISOMetadataExtractor does not set these fields.

            // Using reflection class for attributes which do not have setters.
            $datasetSubmissionReflection = new \ReflectionClass($datasetSubmission);
            $statusReflection = $datasetSubmissionReflection->getProperty('status');
            $statusReflection->setAccessible(true);
            $statusReflection->setValue($datasetSubmission, DatasetSubmission::STATUS_COMPLETE);
            $datasetSubmission->setRestrictions($dataset->getDatasetSubmission()->getRestrictions());
            $datasetSubmission->setCreator($person);
            $dataset->setDatasetSubmission($datasetSubmission);
            $entityManager->persist($datasetSubmission);
            $entityManager->persist($dataset);

            // Echo text for user to know the count and datasets which have been migrated.
            $i++;
            echo "\n Migration complete for " . $dataset->getUdi() . "(" . $i . ")" ;
        }
        echo "\n Migration completed. Now Flushing, This might take a few minutes. Please wait.";
        $entityManager->flush($dataset);

        return 0;
    }

    /**
     * Clears out data properties from a Dataset Submission.
     *
     * @param DatasetSubmission $datasetSubmission The dataset submission that will be cleared.
     *
     * @return void
     */
    private function clearDatasetSubmission(DatasetSubmission &$datasetSubmission)
    {
        $datasetSubmission->getDatasetContacts()->clear();
        $accessor = PropertyAccess::createPropertyAccessor();
        $clearProperties = array(
            'title',
            'shortTitle',
            'abstract',
            'purpose',
            'suppParams',
            'suppInstruments',
            'suppMethods',
            'suppSampScalesRates',
            'suppErrorAnalysis',
            'suppProvenance',
            'referenceDate',
            'referenceDateType',
            'spatialExtent',
            'spatialExtentDescription',
            'temporalExtentDesc',
            'temporalExtentBeginPosition',
            'temporalExtentEndPosition',
            'distributionFormatName',
            'fileDecompressionTechnique',
        );
        foreach ($clearProperties as $property) {
            $accessor->setValue($datasetSubmission, $property, null);
        }
        $emptyProperties = array(
            'themeKeywords',
            'placeKeywords',
            'topicKeywords',
        );
        foreach ($emptyProperties as $property) {
            $accessor->setValue($datasetSubmission, $property, array());
        }
    }
}
