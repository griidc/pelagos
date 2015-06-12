<?php

namespace Pelagos\Component;

/**
 * Class to handle creating, getting, updating, and deleting Persons.
 */
class PersonService extends \Pelagos\Component
{
    /**
     * Method to create a Person object from parameters and persist it.
     *
     * @param string $firstName Person's first name.
     * @param string $lastName Person's last name.
     * @param string $emailAddress Person's email address.
     * @return \Pelagos\Entity\Person The person object that was created and persisted.
     */
    public function createPerson($firstName, $lastName, $emailAddress)
    {
        $person = new \Pelagos\Entity\Person($firstName, $lastName, $emailAddress);
        try {
            $entityManager = $this->getEntityManager();
            $entityManager->persist($person);
            $entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e) {
            throw new \Pelagos\Exception\MissingRequiredFieldPersistenceException($e->getMessage(), $e->getCode(), $e);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new \Pelagos\Exception\RecordExistsPersistenceException($e->getMessage(), $e->getCode(), $e);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Pelagos\Exception\PersistenceException($e->getMessage(), $e->getCode(), $e);
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
