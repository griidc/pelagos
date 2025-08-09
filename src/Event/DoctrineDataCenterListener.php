<?php

namespace App\Event;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\Entity\DataCenter;
use App\Entity\DataRepository;

/**
 * Doctrine Listener class for DataRepository update events.
 */
class DoctrineDataCenterListener
{
    /**
     * On flush pass DataRepository object to updateDataCenter to update the related DataCenter, if necessary.
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
            $this->updateDataCenter($entity, $entityManager);
        }
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->updateDataCenter($entity, $entityManager);
        }
    }

    /**
     * Method to update dataCenter when the corresponding entity in dataRepository changes.
     *
     * @param mixed                  $dataRepository A Doctrine entity (dataRepository).
     * @param EntityManagerInterface $entityManager  The Doctrine entity manager.
     *
     * @return void
     */
    protected function updateDataCenter($dataRepository, EntityManagerInterface $entityManager)
    {
        //reflection class to invoke SET methods in DataCenter
        $reflectionClass = new \ReflectionClass(DataCenter::class);

        if ($dataRepository instanceof DataRepository) {
            $originalDataRepository = $entityManager->getUnitOfWork()->getOriginalEntityData($dataRepository);

            //get datacenter object by email (assume email address is unique)
            $dataCenter = $entityManager->getRepository(DataCenter::class)
                ->findOneBy(['emailAddress' => $originalDataRepository['emailAddress']]);

            if (null !== $dataCenter) {
                //changeSet structure: [ "key" => [ 0: old value, 1: new value] ]
                $changeSet = $entityManager->getUnitOfWork()->getEntityChangeSet($dataRepository);

                //get the array of changeset keys, loop through and updating datacenter by invoking its SET methods
                $changeSetKeys = array_keys($changeSet);
                foreach ($changeSetKeys as $key) {
                    switch ($key) {
                        case 'name':
                        case 'description':
                            $newOrganizationName = $dataRepository->getDescription()
                                . ' (' . $dataRepository->getName() . ')';
                            $dataCenter->setOrganizationName($newOrganizationName);
                            break;
                        case 'url':
                            $dataCenter->setOrganizationUrl($dataRepository->getUrl());
                            break;
                        default:
                            if ($reflectionClass->hasMethod('set' . ucfirst($key))) {
                                //invoke the setter method for the key
                                $reflectionClass->getMethod('set' . ucfirst($key))
                                    ->invoke($dataCenter, $changeSet[$key][1]);
                            }
                            break;
                    }
                }

                $dataCenter->setModifier($dataRepository->getModifier());

                $entityManager->persist($dataCenter);
                $classMetadata = $entityManager->getClassMetadata(DataCenter::class);
                $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $dataCenter);
            }
        }
    }
}
