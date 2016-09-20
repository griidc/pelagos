<?php

namespace Pelagos\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Metadata;

/**
 * Doctrine Listener class for Dataset related events.
 */
class DoctrineDatasetListener
{
    /**
     * Method to update dataset title and abstract when DIF, Dataset Submission, or Metadata changes.
     *
     * @param LifecycleEventArgs $args Doctrine event arguments.
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof DIF
            or $entity instanceof DatasetSubmission
            or $entity instanceof Metadata
            ) {
            $dataset = $entity->getDataset();

            if ($dataset instanceof Dataset) {
                $dataset->updateTitle();
                $dataset->updateAbstract();
                $dataset->setModifier($entity->getModifier());
                $args->getEntityManager()->persist($dataset);
            }
        }
    }
}
