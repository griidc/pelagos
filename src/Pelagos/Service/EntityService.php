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
     * @throws ValidationException When there are validation violations.
     *
     * @return Entity The entity object that was validated.
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
     * @throws MissingRequiredFieldPersistenceException When a required field is missing.
     * @throws RecordExistsPersistenceException         When a uniqueness constrain is violated.
     * @throws PersistenceException                     When a previously uncaught persistence error occurs.
     *
     * @return Entity The entity object that was persisted.
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
     * @throws ArgumentException                  When $id is not a non-negative integer.
     * @throws PersistenceException               When an error occurs retrieving from persistence.
     * @throws RecordNotFoundPersistenceException When no object of the specified class is found for the provided id.
     *
     * @return Entity The entity object with the provided id.
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
     * @param string     $entityClass Entity class to retrieve from.
     * @param array|null $orderBy     Associative array of properties to order by
     *                                (keys = properties, values = ASC/DESC).
     *
     * @throws PersistenceException When an error occurs retrieving from persistence.
     *
     * @return array A list of entity objects of the specified class.
     */
    public function getAll($entityClass, $orderBy = null)
    {
        return $this->getBy($entityClass, array(), $orderBy);
    }

    /**
     * Method to get all Entity objects of the specified class that satisfy a set of criteria.
     *
     * @param string     $entityClass Entity class to retrieve from.
     * @param array      $criteria    The criteria to filter by.
     * @param array|null $orderBy     Associative array of properties to order by
     *                                (keys = properties, values = ASC/DESC).
     *
     * @throws PersistenceException When an error occurs retrieving from persistence.
     *
     * @return array A list of entity objects of the specified class.
     */
    public function getBy($entityClass, array $criteria, $orderBy = null)
    {
        try {
            $fullyQualifiedEntityClass = '\Pelagos\Entity\\' . $entityClass;
            $class = $this->entityManager->getClassMetadata($fullyQualifiedEntityClass);
            foreach (array_keys($criteria) as $property) {
                if (!$class->hasField($property) && !$class->hasAssociation($property)) {
                    unset($criteria[$property]);
                }
            }
            if ($orderBy !== null) {
                foreach (array_keys($orderBy) as $property) {
                    if (!$class->hasField($property) && !$class->hasAssociation($property)) {
                        unset($orderBy[$property]);
                    }
                }
            }
            $entities = $this
                ->entityManager
                ->getRepository($fullyQualifiedEntityClass)
                ->findBy($criteria, $orderBy);
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage());
        }
        return $entities;
    }

    /**
     * Delete an Entity.
     *
     * @param Entity $entity The entity object to delete.
     *
     * @throws PersistenceException When a persistence error occurs.
     *
     * @return Entity The entity object that was deleted.
     */
    public function delete(Entity $entity)
    {
        try {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage(), $e->getCode(), $e);
        }
        return $entity;
    }

    /**
     * Get a list of all distinct values for a property of a given Entity class.
     *
     * @param string $entityClass Entity class to get distinct values from.
     * @param string $property    Property to get distinct values of.
     *
     * @throws \Exception           When Entity $entityClass does not have a mapped property $property.
     * @throws PersistenceException When a database error occurs.
     *
     * @return array List of all distinct values for $property for $entityClass.
     */
    public function getDistinctVals($entityClass, $property)
    {
        $fullyQualifiedEntityClass = '\Pelagos\Entity\\' . $entityClass;
        $class = $this->entityManager->getClassMetadata($fullyQualifiedEntityClass);
        if (!$class->hasField($property) && !$class->hasAssociation($property)) {
            throw new \Exception("Entity \"$entityClass\" has no mapped property \"$property\"");
        }
        try {
            $this->entityManager
                ->getConfiguration()
                ->addCustomHydrationMode(
                    'COLUMN_HYDRATOR',
                    'Pelagos\DoctrineExtensions\Hydrators\ColumnHydrator'
                );
            // Get distinct vals
            $query = $this->entityManager
                ->getRepository($fullyQualifiedEntityClass)
                ->createQueryBuilder('entity')
                ->select('entity.' . $property)
                ->distinct()
                ->orderBy('entity.' . $property)
                ->getQuery();
            return $query->getResult('COLUMN_HYDRATOR');
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage());
        }
    }
}
