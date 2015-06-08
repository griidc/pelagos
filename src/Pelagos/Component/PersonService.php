<?php

namespace Pelagos\Component;

class PersonService extends \Pelagos\Component
{
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
