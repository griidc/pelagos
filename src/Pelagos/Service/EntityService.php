<?php

namespace Pelagos\Service;

use \Doctrine\DBAL\DBALException;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Doctrine\ORM\EntityManager;
use \Pelagos\Entity\Entity;
use \Pelagos\Exception\ArgumentException;
use \Pelagos\Exception\PersistenceException;
use \Pelagos\Exception\MissingRequiredFieldPersistenceException;
use \Pelagos\Exception\RecordExistsPersistenceException;
use \Pelagos\Exception\RecordNotFoundPersistenceException;
use \Pelagos\Exception\ValidationException;

/**
 * Class to handle validating, persisting, and retrieving Pelagos entities.
 */
class EntityService
{
    /**
     * The entity manager to use with this service.
     *
     * @var EntityManager $entityManager
     */
    protected $entityManager;

    /**
     * Constructor for entity service.
     *
     * @param EntityManager $entityManager The entity manager to use with this service.
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Validate an Entity with the provided validator.
     *
     * @param Entity $entity    The entity object to validate.
     * @param mixed  $validator The validator to use for validation.
     *
     * @return Entity The entity object that was validated.
     *
     * @throws ValidationException When there are validation violations.
     */
    public function validate(Entity $entity, $validator)
    {
        $violations = $validator->validate($entity);
        if (count($violations) != 0) {
            throw new ValidationException($violations);
        }
        return $entity;
    }

    /**
     * Persist an Entity.
     *
     * @param Entity $entity The entity object to persist.
     *
     * @return Entity The entity object that was persisted.
     *
     * @throws MissingRequiredFieldPersistenceException When a required field is missing.
     * @throws RecordExistsPersistenceException         When a uniqueness constrain is violated.
     * @throws PersistenceException                     When a previously uncaught persistence error occurs.
     */
    public function persist(Entity $entity)
    {
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
        } catch (NotNullConstraintViolationException $e) {
            throw new MissingRequiredFieldPersistenceException($e->getMessage(), $e->getCode(), $e);
        } catch (UniqueConstraintViolationException $e) {
            throw new RecordExistsPersistenceException($e->getMessage(), $e->getCode(), $e);
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage(), $e->getCode(), $e);
        }
        return $entity;
    }

    /**
     * Method to get an Entity object of the specified class given the provided id.
     *
     * @param string $entityClass Entity class to retrieve from.
     * @param string $id          Entity identifier to retrieve.
     *
     * @return Entity The entity object with the provided id.
     *
     * @throws ArgumentException                  When $id is not a non-negative integer.
     * @throws PersistenceException               When an error occurs retrieving from persistence.
     * @throws RecordNotFoundPersistenceException When no object of the specified class is found for the provided id.
     */
    public function get($entityClass, $id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            $exception = new ArgumentException(
                "$entityClass id must be a non-negative integer"
            );
            $exception->setArgumentName('id');
            $exception->setArgumentValue($id);
            throw $exception;
        }
        try {
            $entity = $this->entityManager->find('\Pelagos\Entity\\' . $entityClass, $id);
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage());
        }
        if (!isset($entity)) {
            $exception = new RecordNotFoundPersistenceException(
                "Could not find a $entityClass with id: $id"
            );
            $exception->setId($id);
            throw $exception;
        }
        return $entity;
    }

    /**
     * Method to get all Entity objects of the specified class.
     *
     * @param string $entityClass Entity class to retrieve from.
     *
     * @return array A list of entity objects of the specified class.
     *
     * @throws PersistenceException When an error occurs retrieving from persistence.
     */
    public function getAll($entityClass)
    {
        try {
            $entities = $this->entityManager->getRepository('\Pelagos\Entity\\' . $entityClass)->findAll();
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage());
        }
        return $entities;
    }

    /**
     * Method to get all Entity objects of the specified class that satisfy a set of criteria.
     *
     * @param string $entityClass Entity class to retrieve from.
     * @param array  $criteria    The criteria to filter by.
     *
     * @return array A list of entity objects of the specified class.
     *
     * @throws PersistenceException When an error occurs retrieving from persistence.
     */
    public function getBy($entityClass, array $criteria)
    {
        try {
            $fullyQualifiedEntityClass = '\Pelagos\Entity\\' . $entityClass;
            $class = $this->entityManager->getClassMetadata($fullyQualifiedEntityClass);
            foreach (array_keys($criteria) as $property) {
                if (!$class->hasField($property) && !$class->hasAssociation($property)) {
                    unset($criteria[$property]);
                }
            }
            $entities = $this->entityManager->getRepository($fullyQualifiedEntityClass)->findBy($criteria);
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage());
        }
        return $entities;
    }
}
