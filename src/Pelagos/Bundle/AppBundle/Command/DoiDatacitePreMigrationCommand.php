<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DOI;
use Pelagos\Entity\DIF;

use Pelagos\Util\DOIutil;

/**
 * Pre-migration Script for moving from EZid Library to Datacite.
 *
 * @see ContainerAwareCommand
 */
class DoiDatacitePreMigrationCommand extends ContainerAwareCommand
{
    /**
     * Output Interface variable.
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
            ->setName('dataset-doi:pre-migration-script')
            ->setDescription('Make DOI public for all submitted, accepted but restricted datasets.');
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
        $this->output = $output;
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        //Make submitted datasets(Reserved DOI's) to public.
        $this->makeSubmittedUnavailable($entityManager);

        //Delete Doi property from Dataset which are DIF approved.
        $this->deleteDoiDif($entityManager);
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
    private function makeSubmittedUnavailable(EntityManager $entityManager)
    {
        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array(
            'datasetStatus' => array(
                Dataset::DATASET_STATUS_SUBMITTED,
                Dataset::DATASET_STATUS_ACCEPTED,
                Dataset::DATASET_STATUS_BACK_TO_SUBMITTER
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
                throw new \Exception('Unable to get DOI Metadata ' . $e->getMessage());
            }

            if ($this->validatePublish($dataset, $doiBeforeStatus)) {
                //Publishing datasets to public status
                if ($doiBeforeStatus === DOI::STATUS_RESERVED) {
                    $doiUtil->publishDOI($doiId, DOI::STATUS_PUBLIC);
                    $doi->setStatus($status);
                    $doi->setPublicDate(new \DateTime);
                    $doi->setModifier($dataset->getModifier());
                    $this->output->writeln('Setting DOI status ' . $dataset->getId() . ' (' . $dataset->getUdi() . ") to public.\n");
                } else {
                    $this->output->writeln('Already in the public state ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n");
                }

                $doiAfterMetadata = $doiUtil->getDOIMetadata($doi);
                $doiStatus = $doiAfterMetadata['_status'];

                //Publishing datasets to unavailable status
                if ($doiStatus === DOI::STATUS_PUBLIC) {
                    $doiUtil->publishDOI($doiId, DOI::STATUS_UNAVAILABLE);
                    $doi->setStatus($status);
                    $doi->setPublicDate(new \DateTime);
                    $doi->setModifier($dataset->getModifier());
                    $this->output->writeln('Setting DOI status ' . $dataset->getId() . ' (' . $dataset->getUdi() . ") to public.\n");
                } else {
                    $this->output->writeln('Already in the public state ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n");
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
        if (in_array(
            $dataset->getDatasetStatus(),
            [Dataset::DATASET_STATUS_SUBMITTED, Dataset::DATASET_STATUS_BACK_TO_SUBMITTER]
        )
        ) {
            // Need to publish all the datasets which are submitted and in reserved state
            return true;
        } elseif ($dataset->getDatasetStatus() === Dataset::DATASET_STATUS_ACCEPTED and
            $dataset->getDatasetSubmission()->getRestrictions() === DatasetSubmission::RESTRICTION_RESTRICTED and
            $doiStatus === DOI::STATUS_RESERVED) {
            // Need to publish accepted datasets which are restricted and doi status is reserved.
            return true;
        }
        return false;
    }

    /**
     * Deletes DOI property from Dataset and object for DIF approved datasets.
     *
     * @param EntityManager $entityManager An instance of EntityManager.
     *
     * @throws ORMException        Exception thrown when get DOI fails.
     * @throws ReflectionException Exception thrown when Reflection class fails.
     *
     * @return void
     */
    private function deleteDoiDif(EntityManager $entityManager)
    {
        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array(
            'datasetStatus' => array(
                Dataset::DATASET_STATUS_NONE
            )
        ));

        foreach ($datasets as $dataset) {
            if ($dataset->getIdentifiedStatus() === DIF::STATUS_APPROVED) {
                // Using reflection class for attributes which do not have setters.
                $doi = $dataset->getDoi();
                try {
                    $datasetReflection = new \ReflectionClass($dataset);
                    $doiReflection = $datasetReflection->getProperty('doi');
                    $doiReflection->setAccessible(true);
                    $doiReflection->setValue($dataset, null);
                    $this->output->writeln('Attempting to Delete Doi for Dataset ' . $dataset->getId());
                } catch (\ReflectionException $exception) {
                    throw new ReflectionException('Reflection class failed ' . $exception->getMessage());
                }

                try {
                    $entityManager->persist($dataset);
                    $entityManager->remove($doi);
                    $entityManager->flush();
                    $this->output->writeln('Doi  Deleted for Dataset ' . $dataset->getId());
                } catch (ORMException $e) {
                    throw new ORMException('Entity manager failed ' . $e->getMessage());
                }
            }
        }
    }
}
