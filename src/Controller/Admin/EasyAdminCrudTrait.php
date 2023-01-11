<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Trait for Easy admin Crud Controllers.
 */
trait EasyAdminCrudTrait
{
    /**
     * Update the Crud entity.
     *
     * @param EntityManagerInterface $entityManager  The Entity Manager.
     * @param mixed                  $entityInstance The entity to update.
     *
     * @return void
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setModifier($this->getUser()->getPerson());
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    /**
     * Crud delete an entity.
     *
     * @param EntityManagerInterface $entityManager  The Entity Manager.
     * @param mixed                  $entityInstance The entity to delete.
     *
     * @return void
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            $entityManager->remove($entityInstance);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException('Unable to delete. Reason:' . $e->getMessage());
        }

        $entityManager->flush();
    }
}
