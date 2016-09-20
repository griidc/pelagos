<?php
namespace Pelagos\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;


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
     * To be updated Datasets.
     *
     * @var array
     */
    private $datasets = array();

    /**
     * This is the doctrine event callback for Pre Persist.
     *
     * @param LifecycleEventArgs $args Doctrine event arguments.
     *
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateDataset($args);
    }

    /**
     * This is the doctrine event callback for Pre Update.
     *
     * @param LifecycleEventArgs $args Doctrine event arguments.
     *
     * @return void
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->updateDataset($args);
    }

    /**
     * Method to update dataset title and abstract when DIF, Dataset Submission, or Metadata changes.
     *
     * @param LifecycleEventArgs $args Doctrine event arguments.
     *
     * @return void
     */
    public function updateDataset(LifecycleEventArgs $args)
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
                // Add the dataset to a list, to later persist and flush in Post.
                $this->datasets[] = $dataset;
            }
        }
    }

    /**
     * This is the doctrine event callback for Post Update.
     *
     * @param LifecycleEventArgs $args Life Cycle Event Arguments.
     *
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->persistAndFlushDataset($args);
    }

    /**
     * This is the doctrine event callback for Post Persist.
     *
     * @param LifecycleEventArgs $args Life Cycle Event Arguments.
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->persistAndFlushDataset($args);
    }

    /**
     * This method persists and flushes the dataset.
     *
     * @param LifecycleEventArgs $args Life Cycle Event Arguments.
     *
     * @return void
     */
    public function persistAndFlushDataset(LifecycleEventArgs $args)
    {
        if (count($this->datasets) > 0) {
            $entityManager = $args->getEntityManager();
            foreach ($this->datasets as $dataset) {
                $entityManager->persist($dataset);
            }
            $entityManager->flush();
        }
    }
}
