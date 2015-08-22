<?php

namespace Pelagos\Component;

use \Pelagos\HTTPStatus;
use \Pelagos\Service\EntityService;
use \Symfony\Component\Validator\Validation;
use \Pelagos\Exception\ArgumentException;
use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Pelagos\Exception\MissingRequiredFieldPersistenceException;
use \Pelagos\Exception\RecordExistsPersistenceException;
use \Pelagos\Exception\RecordNotFoundPersistenceException;
use \Pelagos\Exception\PersistenceException;
use \Pelagos\Exception\ValidationException;

class EntityWebService extends \Pelagos\Component
{
    protected $entityService;

    public function __construct()
    {
        // call constructor for \Pelagos\Component
        parent::__construct();
        // get an entityService instance
        $this->entityService = new EntityService($this->getEntityManager());
    }

    public function updateValidateAndPersist(\Pelagos\Entity\Entity $entity, array $updates, $action)
    {
        if ($action == 'create') {
            $successCode = 201;
        } else {
            $successCode = 200;
        }
        $entityName = preg_replace('/^.*\\\/', '', get_class($entity));
        // set any empty properties to null
        foreach ($updates as $property => $value) {
            if (empty($value)) {
                $updates[$property] = null;
            }
        }
        try {
            // update, validate, and persist the entity
            $entity = $this->entityService->persist(
                $this->entityService->validate(
                    $entity->update($updates),
                    Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator()
                )
            );
            $status = new HTTPStatus(
                201,
                sprintf(
                    'A %s has been successfully %sed with at ID of %d.',
                    $entityName,
                    $action,
                    $entity->getId()
                )
            );
        } catch (ValidationException $e) {
            $violations = array();
            foreach ($e->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $status = new HTTPStatus(
                400,
                "Cannot $action $entityName because: " . join(', ', $violations)
            );
        } catch (MissingRequiredFieldPersistenceException $e) {
            $status = new HTTPStatus(400, "Cannot $action $entityName because a required field is missing.");
        } catch (RecordExistsPersistenceException $e) {
            $status = new HTTPStatus(409, "Cannot $action $entityName: " . $e->getDatabaseErrorMessage());
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        return $status;
    }
}
