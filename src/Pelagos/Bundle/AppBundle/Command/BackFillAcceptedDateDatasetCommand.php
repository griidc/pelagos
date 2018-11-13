<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;

/**
 * Back fill script for accepted date for Dataset entity.
 *
 * @see ContainerAwareCommand
 */
class BackFillAcceptedDateDatasetCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset:backfill-accepted-date')
            ->setDescription('Backfill Dataset accepted date for approved datasets.');
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

        $auditReader = $this->getContainer()->get('simplethings_entityaudit.reader');

        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array('datasetStatus' => Dataset::DATASET_STATUS_ACCEPTED));
        $count = 0;
        $acceptedDateTimeStamp = new \DateTime();

        foreach ($datasets as $dataset) {
            $auditRevisionFinder = $auditReader->findRevisions(
                'Pelagos\Entity\Dataset',
                $dataset->getId()
            );

            $numberOfRevisions = count($auditRevisionFinder);
            if ($numberOfRevisions > 1) {
                for ($i = ($numberOfRevisions - 1); $i > 0; $i --) {
                    // The revisions are ordered by latest first.
                    $oldRevision = $auditRevisionFinder[$i];
                    $newRevision = $auditRevisionFinder[($i - 1)];

                    $articleDiff = $auditReader->diff(
                        'Pelagos\Entity\Dataset',
                        $dataset->getId(),
                        $oldRevision->getRev(),
                        $newRevision->getRev()
                    );

                    if ($i === ($numberOfRevisions - 1) and $articleDiff['datasetStatus']['same'] === Dataset::DATASET_STATUS_ACCEPTED) {
                        $acceptedDateTimeStamp = $articleDiff['modificationTimeStamp']['old'];
                    }

                    if ($articleDiff['datasetStatus']['new'] === Dataset::DATASET_STATUS_ACCEPTED) {
                        if (!empty($articleDiff['modificationTimeStamp']['new'])) {
                            $acceptedDateTimeStamp = $articleDiff['modificationTimeStamp']['new'];
                        } else {
                            $acceptedDateTimeStamp = $articleDiff['modificationTimeStamp']['same'];
                        }
                    }
                }
            } else {
                $acceptedDateTimeStamp = $dataset->getModificationTimeStamp();
            }

            if ($acceptedDateTimeStamp instanceof \DateTime) {

                //This is a workaround used to get a new DIF object from the entityManager with the same Id,
                //because of the unknown behavior of auditReader on the previous DIF object.
                $entityManager->clear();
                $newDataset = $entityManager->getRepository(Dataset::class)->findOneBy(array('id' => $dataset->getId()));

                $newDataset->setAcceptedDate($acceptedDateTimeStamp);
                $output->writeln('Accepted date back-filled for dataset: ' . $dataset->getId());
                $entityManager->persist($newDataset);
                $entityManager->flush();
                $count++;
            } else {
                $output->writeln('Modification Time stamp not an instance of DateTime for Dataset Id: ' . $dataset->getId());
            }
            $acceptedDateTimeStamp = null;
        }

        $output->writeln('Total number of datasets which got back-filled: ' . $count);
    }
}
