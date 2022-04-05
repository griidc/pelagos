<?php

namespace App\Event;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\InformationProduct;
use App\Repository\InformationProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Listener class for Information Product and Types related events.
 */
class InformationProductListener
{
    /**
     * Listenerer on Pre-Remove event.
     *
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof DigitalResourceTypeDescriptor) {
            /** @var EntityManagerInterface $entityManager */
            $entityManager = $args->getObjectManager();

            /** @var InformationProductRepository $repository */
            $repository = $entityManager->getRepository(InformationProduct::class);

            $informationProducts = $repository->findByDigitalResourceTypeDescriptor($entity);

            if (count($informationProducts) > 0) {
                throw new ORMException('This Digitial Resource Type Descriptor is associated with an Information Product');
            }
        }
    }



}
