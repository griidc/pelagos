<?php

namespace App\Controller\Admin;

use App\Exception\NotDeletableException;
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
     * @param EntityManagerInterface $entityManager  the Entity Manager
     * @param mixed                  $entityInstance the entity to update
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setModifier($this->getUser()->getPerson());
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Crud delete an entity.
     *
     * @param EntityManagerInterface $entityManager  the Entity Manager
     * @param mixed                  $entityInstance the entity to delete
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            $entityManager->remove($entityInstance);
        } catch (NotDeletableException $e) {
            throw new AccessDeniedHttpException('Unable to delete. Reason:' . $e->getMessage());
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    /**
     * Crud update an entity.
     *
     * @param string $entityFqcn entity class name
     */
    public function createEntity(string $entityFqcn)
    {
        $entity = parent::createEntity($entityFqcn);
        $entity->setCreator($this->getUser()->getPerson());

        return $entity;
    }
}
