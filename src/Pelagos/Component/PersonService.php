<?php

namespace Pelagos\Component;

use \Pelagos\Entity\Person;
use \Pelagos\Exception\ValidationException;
use \Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use \Pelagos\Exception\MissingRequiredFieldPersistenceException;
use \Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use \Pelagos\Exception\RecordExistsPersistenceException;
use \Doctrine\DBAL\DBALException;
use \Pelagos\Exception\PersistenceException;

/**
 * Class to handle creating, getting, updating, and deleting Persons.
 */
class PersonService extends \Pelagos\Component
{
    /**
     * Validate a Person with the provided validator.
     *
     * @param Person $person    The person object to validate.
     * @param mixed  $validator The validator to use for validation.
     *
     * @return Person The person object that was validated.
     *
     * @throws ValidationException When there are validation violations.
     */
    public function validate(Person $person, $validator)
    {
        $violations = $validator->validate($person);
        if (count($violations) != 0) {
            throw new ValidationException($violations);
        }
        return $person;
    }

    /**
     * Persist a Person.
     *
     * @param Person $person The Person object to persist.
     *
     * @return Person The person object that was persisted.
     *
     * @throws MissingRequiredFieldPersistenceException When a required field is missing.
     * @throws RecordExistsPersistenceException         When a uniqueness constrain is violated.
     * @throws PersistenceException                     When a previously uncaught persistence error occurs.
     */
    public function persist(Person $person)
    {
        try {
            $entityManager = $this->getEntityManager();
            $entityManager->persist($person);
            $entityManager->flush();
        } catch (NotNullConstraintViolationException $e) {
            throw new MissingRequiredFieldPersistenceException($e->getMessage(), $e->getCode(), $e);
        } catch (UniqueConstraintViolationException $e) {
            throw new RecordExistsPersistenceException($e->getMessage(), $e->getCode(), $e);
        } catch (DBALException $e) {
            throw new PersistenceException($e->getMessage(), $e->getCode(), $e);
        }
        return $person;
    }

    /**
     * Method to get a Person object given the provided id.
     *
     * @param string $id Person's id.
     * @return \Pelagos\Entity\Person The person object with the provided id.
     */
    public function getPerson($id)
    {
        if (!preg_match('/^\d+$/', $id)) {
            $exception = new \Pelagos\Exception\ArgumentException(
                "Person id must be a non-negative integer"
            );
            $exception->setArgumentName('id');
            $exception->setArgumentValue($id);
            throw $exception;
        }
        try {
            $entityManager = $this->getEntityManager();
            $person = $entityManager->find('\Pelagos\Entity\Person', $id);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Pelagos\Exception\PersistenceException($e->getMessage());
        }
        if (!isset($person)) {
            $exception = new \Pelagos\Exception\RecordNotFoundPersistenceException(
                "Could not find a Person with id: $id"
            );
            $exception->setId($id);
            throw $exception;
        }
        return $person;
    }

    /**
     * Method to update a Person object given the provided id.
     *
     * @param string $id Person's id.
     * @return \Pelagos\Entity\Person The person object with the provided id.
     */
    public function updatePerson($id, $updates)
    {
        if (!preg_match('/^\d+$/', $id)) {
            $exception = new \Pelagos\Exception\ArgumentException(
                "Person id must be a non-negative integer"
            );
            $exception->setArgumentName('id');
            $exception->setArgumentValue($id);
            throw $exception;
        }
        try {
            $entityManager = $this->getEntityManager();
            $person = $entityManager->find('\Pelagos\Entity\Person', $id);
            if (!isset($person)) {
                throw new \Pelagos\Exception\RecordNotFoundPersistenceException("Person with id $id not found");
            }
            $person->update($updates);
            $entityManager->persist($person);
            $entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e) {
            throw new \Pelagos\Exception\MissingRequiredFieldPersistenceException($e);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new \Pelagos\Exception\RecordExistsPersistenceException($e);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Pelagos\Exception\PersistenceException($e->getMessage());
        }
        return $person;
    }
}
