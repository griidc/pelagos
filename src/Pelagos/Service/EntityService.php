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
    protected $entityManager;

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
}
