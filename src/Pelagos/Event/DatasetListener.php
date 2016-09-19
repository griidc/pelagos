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
class DatasetListener
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

            $dataset->updateTitle();
            $dataset->updateAbstract();
            $args->getEntityManager()->persist($dataset);
        }
    }
}
