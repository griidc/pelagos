<?php

namespace Pelagos\Bundle\AppBundle\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

use Pelagos\Entity\Entity;

/**
 * A handler for entities.
 */
class EntityHandler
{
    /**
     * The entity manager to use in this entity handler.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * The authorization checker to use in this entity handler.
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * Constructor for EntityHandler.
     *
     * @param EntityManager                 $entityManager        The entity manager to use.
     * @param AuthorizationCheckerInterface $authorizationChecker The authorization checker to use.
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Return an entity of $entityClass identified by $id.
     *
     * @param string  $entityClass The type of entity to retrieve.
     * @param integer $id          The id of the entity to retrieve.
     *
     * @return Entity|null The entity.
     */
    public function get($entityClass, $id)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->find($id);
    }

    /**
     * Return all entities of $entityClass.
     *
     * @param string $entityClass The type of entity to retrieve.
     *
     * @return Collection A collection of entities.
     */
    public function getAll($entityClass)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->findAll();
    }

    /**
     * Return all entities of $entityClass filtered by $criteria and sorted by $orderBy.
     *
     * @param string     $entityClass The type of entity to retrieve.
     * @param array      $criteria    The criteria to filter by.
     * @param array|null $orderBy     The properties to sort by.
     *
     * @return Collection A collection of entities.
     */
    public function getBy($entityClass, array $criteria, $orderBy = null)
    {
        return $this->entityManager
            ->getRepository($entityClass)
            ->findBy($criteria, $orderBy);
    }

    /**
     * Create a new entity.
     *
     * @param Entity $entity The entity to create.
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to create the entity.
     *
     * @return Entity The new entity.
     */
    public function create(Entity $entity)
    {
        if (!$this->authorizationChecker->isGranted('CAN_CREATE', $entity)) {
            throw new AccessDeniedException(
                'You do not have sufficient privileges to create this ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        return $entity;
    }

    /**
     * Update an entity.
     *
     * @param Entity $entity The entity to update.
     *
     * @throws AccessDeniedException When the user does not have sufficient privileges to update the entity.
     *
     * @return Entity The updated entity.
     */
    public function update(Entity $entity)
    {
        if (!$this->authorizationChecker->isGranted('CAN_EDIT', $entity)) {
            throw new AccessDeniedException(
                'You do not have sufficient privileges to edit this ' . $entity::FRIENDLY_NAME . '.'
            );
        }
        $this->entityManager->persist($entity);
        $this->entityManager->flush($entity);
        return $entity;
    }
}
