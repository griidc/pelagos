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
            throw new \Pelagos\Exception\MissingRequiredFieldPersistenceException($e);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new \Pelagos\Exception\RecordExistsPersistenceException($e);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Pelagos\Exception\PersistenceException($e->getMessage());
        }
        return $person;
    }
}
