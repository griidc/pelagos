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

        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array('datasetStatus' => Dataset::DATASET_STATUS_ACCEPTED));
        $count = 0;
        $acceptedDateTimeStamp = new \DateTime();

        foreach ($datasets as $dataset) {
            $queryString = 'select id, rev, dataset_id, dataset_status, modification_time_stamp 
                            from dataset_submission_audit where dataset_id = :datasetId order by rev desc, id desc';
            $params = [
                'datasetId' => $dataset->getId(),
            ];
            $params['datasetId'] = $dataset->getId();
            $stmt = $entityManager->getConnection()->prepare($queryString);
            $stmt->execute($params);
            $auditRevisionFinder = $stmt->fetchAll();

            $numberOfRevisions = count($auditRevisionFinder);
            if ($numberOfRevisions > 1) {
                for ($i = ($numberOfRevisions - 1); $i > 0; $i --) {
                    // The revisions are ordered by latest first.
                    $oldRevision = $auditRevisionFinder[$i];
                    $newRevision = $auditRevisionFinder[($i - 1)];

                    $articleDiff = $this->diff($oldRevision, $newRevision);

                    if ($i === ($numberOfRevisions - 1) and $articleDiff['dataset_status']['same'] === Dataset::DATASET_STATUS_ACCEPTED) {
                        $acceptedDateTimeStamp = $articleDiff['modification_time_stamp']['old'];
                    }

                    if ($articleDiff['dataset_status']['new'] === Dataset::DATASET_STATUS_ACCEPTED) {
                        if (!empty($articleDiff['modification_time_stamp']['new'])) {
                            $acceptedDateTimeStamp = $articleDiff['modification_time_stamp']['new'];
                        } else {
                            $acceptedDateTimeStamp = $articleDiff['modification_time_stamp']['same'];
                        }
                    }
                }
            } else {
                $acceptedDateTimeStamp = $dataset->getModificationTimeStamp();
            }

            $acceptedDateTimeStamp = new \DateTime($acceptedDateTimeStamp);

            if ($acceptedDateTimeStamp instanceof \DateTime) {
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

    /**
     * Creates a diff between 2 arrays.
     *
     * @param array $oldData Array which contains the old data.
     * @param array $newData Array which contains the new data.
     *
     * @return array
     */
    public function diff($oldData, $newData)
    {
        $diff = array();

        $keys = array_keys($oldData + $newData);
        foreach ($keys as $field) {
            $old = array_key_exists($field, $oldData) ? $oldData[$field] : null;
            $new = array_key_exists($field, $newData) ? $newData[$field] : null;

            if ($old == $new) {
                $row = array('old' => '', 'new' => '', 'same' => $old);
            } else {
                $row = array('old' => $old, 'new' => $new, 'same' => '');
            }

            $diff[$field] = $row;
        }

        return $diff;
    }
}
