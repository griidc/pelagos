<?php

namespace App\Event;

use App\Message\DoiMessage;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

/**
 * Doctrine Listener class for Dataset related events.
 */
class DoctrineDatasetListener
{
    /**
     * Symfony messenger bus interface.
     *
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * Memcached adapter instance.
     *
     * @var \Memcached
     */
    protected $cache;

    /**
     * DoctrineDatasetListener constructor.
     *
     * @param MessageBusInterface $messageBus Symfony messenger bus interface.
     * @param MemcachedAdapter    $cache      Memcached adapter instance.
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
        $this->cache = MemcachedAdapter::createConnection('memcached://localhost');
    }

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
     * @param mixed                  $entity        A Doctrine entity.
     * @param EntityManagerInterface $entityManager The Doctrine entity manager.
     *
     * @return void
     */
    protected function updateDataset($entity, EntityManagerInterface $entityManager)
    {
        if (
            $entity instanceof DIF
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
                if ($entity instanceof DatasetSubmission) {
                    $entity->setDatasetFileSize($dataset->getTotalFileSize());
                }
            }
            // Invalidate the cache entry for the dataset
            $cacheKey = 'gml2geojson' . $dataset->getUdi();
            $this->cache->delete($cacheKey);
        }
    }

    /**
     * Post update method for dataset entity, to publish/update Doi.
     *
     * @param Dataset            $dataset An instance of Dataset entity.
     * @param LifecycleEventArgs $args    Doctrine life cycle event args.
     *
     * @return void
     */
    public function postUpdate(Dataset $dataset, LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Dataset) {
            if (
                ($entity->getDatasetSubmission() instanceof DatasetSubmission and
                in_array(
                    $entity->getAvailabilityStatus(),
                    [
                        DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE
                    ]
                )
                ) or
                ($entity->getDif() instanceof DIF and $entity->getIdentifiedStatus() === DIF::STATUS_APPROVED)
            ) {
                $doiMessage = new DoiMessage($entity->getId(), DoiMessage::ISSUE_OR_UPDATE);
                $this->messageBus->dispatch($doiMessage);
            }
        }
    }
}
