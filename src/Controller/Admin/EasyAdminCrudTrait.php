<?php

namespace App\Controller\Admin;

use App\Entity\Account;
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
        /** @var ?Account $account */
        $account = $this->getUser();
        if ($account instanceof Account) {
            $entityInstance->setModifier($account->getPerson());
        }
        $entityManager->persist($entityInstance);
        $entityManager->flush();
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

        $entityManager->flush();
    }
}
