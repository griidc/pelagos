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
     * @throws \Exception When dataset not found.
     * @throws \Exception When datasetSubmission not found.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        //Recreate the DOIs which we deleted.
        $this->recreateDoi($entityManager);

        return 0;
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
    private function recreateDoi(EntityManager $entityManager)
    {
        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array(
            'metadataStatus' => array(
                DatasetSubmission::METADATA_STATUS_NONE
            )
        ));

        foreach ($datasets as $dataset) {
            if ($dataset->getIdentifiedStatus() === DIF::STATUS_SUBMITTED) {
                $doi = $dataset->getDoi();
                // Using reflection class for attributes which do not have setters.
                if (!$doi instanceof DOI) {
                    try {
                        $doiUtil = new DOIutil();
                        $issuedDoi = $doiUtil->createDOI(
                            'https://data.gulfresearchinitiative.org/data/' . $dataset->getUdi(),
                            $dataset->getAuthors(),
                            $dataset->getTitle(),
                            'Harte Research Institute',
                            $dataset->getReferenceDateYear()
                        );
                    } catch (\Exception $exception) {
                        echo ('Error requesting DOI: ' . $exception->getMessage());
                        return;
                    }

                    $doi = new DOI($issuedDoi);
                    $doi->setCreator($dataset->getModifier());
                    $doi->setModifier($dataset->getModifier());
                    $dataset->setDoi($doi);
                    $entityManager->persist($dataset);
                    $entityManager->flush();
                }
            }
        }
    }
}
