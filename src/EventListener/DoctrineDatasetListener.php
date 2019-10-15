<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;

/**
 * Doctrine Listener class for Dataset related events.
 */
class DoctrineDatasetListener
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
     * @param mixed $entity A Doctrine entity.
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    protected function updateDataset($entity, EntityManagerInterface $entityManager)
    {
        if ($entity instanceof DIF
            or $entity instanceof DatasetSubmission
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
}
