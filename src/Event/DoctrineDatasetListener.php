<?php

namespace App\Event;

use App\Util\RabbitPublisher;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
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
     * Custom rabbitmq publisher.
     *
     * @var RabbitPublisher
     */
    protected $publisher;

    /**
     * DoctrineDatasetListener constructor.
     *
     * @param RabbitPublisher $publisher An AMQP/RabbitMQ Producer.
     */
    public function __construct(RabbitPublisher $publisher)
    {
        $this->publisher = $publisher;
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
        $entityManager = $args->getObjectManager();
        if ($entity instanceof Dataset) {
            if ($entity->getDatasetSubmission() instanceof DatasetSubmission) {
                if (in_array(
                    $entity->getAvailabilityStatus(),
                    [
                        DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE
                    ]
                )
                ) {
                    $this->publisher->publish($dataset->getId(), RabbitPublisher::DOI_PRODUCER, 'update');
                }
            } else {
                if ($entity->getDif() instanceof DIF and $entity->getIdentifiedStatus() === DIF::STATUS_APPROVED) {
                    $this->publisher->publish($dataset->getId(), RabbitPublisher::DOI_PRODUCER, 'issue');
                }
            }
        }
    }
}
