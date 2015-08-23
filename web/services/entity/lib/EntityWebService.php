<?php

namespace Pelagos\Component;

use \Pelagos\HTTPStatus;
use \Pelagos\Entity\Entity;
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

/**
 * Utility class for the entity web service.
 */
class EntityWebService extends \Pelagos\Component
{
    /**
     * An instance of Pelagos\Service\EntityService.
     *
     * @var EntityService $entityService
     * @access protected
     */
    protected $entityService;

    /**
     * Constructor for EntityWebService.
     *
     * @access public
     */
    public function __construct()
    {
        // Call constructor for \Pelagos\Component
        parent::__construct();
        // Create an EntityService instance
        $this->entityService = new EntityService($this->getEntityManager());
    }

    /**
     * Getter for entityService.
     *
     * @access public
     *
     * @return EntityService An instance of Pelagos\Service\EntityService.
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * Apply updates to an entity, validate it, and persist it.
     *
     * @param Entity $entity  The entity to operate on.
     * @param array  $updates The update to apply to $entity.
     * @param string $action  The name of the action calling this method.
     *
     * @access public
     *
     * @return HTTPStatus Object containing the result of the operation.
     */
    public function updateValidateAndPersist(Entity $entity, array $updates, $action)
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
                $successCode,
                sprintf(
                    'A %s has been successfully %sd with at ID of %d.',
                    $entityName,
                    $action,
                    $entity->getId()
                ),
                $entity
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
