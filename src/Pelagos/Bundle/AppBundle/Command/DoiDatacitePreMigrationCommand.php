<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DOI;

use Pelagos\Util\DOIutil;

/**
 * Pre-migration Script for moving from EZid Library to Datacite.
 *
 * @see ContainerAwareCommand
 */
class DoiDatacitePreMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset-doi:pre-migration-script')
            ->setDescription('Make DOI public for all submitted, accepted but restricted datasets.');
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

        //Make submitted datasets(Reserved DOI's) to public.
        $this->makeSubmittedPublic($entityManager);

        return 0;
    }

    /**
     * Makes submitted datasets DOI to public and then unavailable.
     *
     * @param EntityManager $entityManager An instance of EntityManager.
     *
     * @throws \Exception Exception thrown when get DOI fails.
     *
     * @return void
     */
    private function makeSubmittedPublic(EntityManager $entityManager)
    {
        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array(
            'metadataStatus' => array(
                DatasetSubmission::METADATA_STATUS_SUBMITTED,
                DatasetSubmission::METADATA_STATUS_ACCEPTED
            )
        ));

        $status = DOI::STATUS_PUBLIC;

        foreach ($datasets as $dataset) {

            $doiUtil = new DOIutil;
            $doi = $dataset->getDoi();
            $doiId = $doi->getDoi();

            try {
                $doiBeforeMetadata = $doiUtil->getDOIMetadata($doi);
                $doiBeforeStatus = $doiBeforeMetadata['_status'];
            } catch (\Exception $e) {
                throw new \Exception('Unable to get DOI Metadata' . $e->getMessage());
            }

            if ($this->validatePublish($dataset, $doiBeforeStatus)) {
                //Publishing datasets to public status
                if ($doiBeforeStatus === DOI::STATUS_RESERVED) {
                    $doiUtil->publishDOI($doiId, DOI::STATUS_PUBLIC);
                    $doi->setStatus($status);
                    $doi->setPublicDate(new \DateTime);
                    $doi->setModifier($dataset->getModifier());
                    echo 'Setting DOI status ' . $dataset->getId() . ' (' . $dataset->getUdi() . ") to public.\n";
                } else {
                    echo 'Already in the public state ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n";
                }

                $doiAfterMetadata = $doiUtil->getDOIMetadata($doi);
                $doiStatus = $doiAfterMetadata['_status'];

                //Publishing datasets to unavailable status
                if ($doiStatus === DOI::STATUS_PUBLIC) {
                    $doiUtil->publishDOI($doiId, DOI::STATUS_UNAVAILABLE);
                    $doi->setStatus($status);
                    $doi->setPublicDate(new \DateTime);
                    $doi->setModifier($dataset->getModifier());
                    echo 'Setting DOI status ' . $dataset->getId() . ' (' . $dataset->getUdi() . ") to public.\n";
                } else {
                    echo 'Already in the public state ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n";
                }
            }
        }
    }

    /**
     * Validate publish for DOI for the dataset.
     *
     * @param Dataset $dataset   The dataset.
     * @param string  $doiStatus The status of the DOI for the dataset.
     *
     * @return boolean
     */
    private function validatePublish(Dataset $dataset, $doiStatus)
    {
        if ($dataset->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_SUBMITTED) {
            // Need to publish all the datasets which are submitted and in reserved state
            return true;
        } elseif ($dataset->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED and
            $dataset->getDatasetSubmission()->getRestrictions() === DatasetSubmission::RESTRICTION_RESTRICTED and
            $doiStatus === DOI::STATUS_RESERVED) {
            // Need to publish accepted datasets which are restricted and doi status is reserved.
            return true;
        }
        return false;
    }
}
