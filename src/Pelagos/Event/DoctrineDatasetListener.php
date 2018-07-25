<?php

namespace Pelagos\Event;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Metadata;

/**
 * Doctrine Listener class for Dataset related events.
 */
class DoctrineDatasetListener extends EventListener
{
    /**
     * On flush pass entity to updateDataset to update the related Dataset, if necessary.
     *
     * @param OnFlushEventArgs $args The onFlush event arguments.
     *
     * @return void
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->updateDataset($entity, $entityManager);
        }
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->updateDataset($entity, $entityManager);
        }
    }

    /**
     * Method to update dataset title and abstract when DIF, Dataset Submission, or Metadata changes.
     *
     * @param mixed         $entity        A Doctrine entity.
     * @param EntityManager $entityManager The Doctrine entity manager.
     *
     * @return void
     */
    protected function updateDataset($entity, EntityManager $entityManager)
    {
        if ($entity instanceof DIF
            or $entity instanceof DatasetSubmission
            or $entity instanceof Metadata
            ) {
            $dataset = $entity->getDataset();

            if ($dataset instanceof Dataset) {
                $dataset->updateTitle();
                $dataset->updateAbstract();
                $dataset->setModifier($entity->getModifier());
                $entityManager->persist($dataset);
                $classMetadata = $entityManager->getClassMetadata(Dataset::class);
                $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $dataset);
            }
        }
    }

    /**
     * On dataset delete, delete DOI is called.
     *
     * @param EntityEvent $event A Doctrine entity.
     *
     * @return void
     */
    public function onDeleteDoi(EntityEvent $event)
    {
        $dataset = $event->getEntity();
        $this->producer->publish($dataset->getDoi()->getDoi(), 'delete');
    }
}
