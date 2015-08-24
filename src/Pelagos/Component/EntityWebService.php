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
    protected $slim;

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
    public function __construct(\Slim\Slim $slim)
    {
        // Call constructor for \Pelagos\Component
        parent::__construct();
        // Save the Slim instance
        $this->slim = $slim;
        // Create an EntityService instance
        $this->entityService = new EntityService($this->getEntityManager());
    }

    /**
     * Getter for entityService.
     *
     * @access protected
     *
     * @return EntityService An instance of Pelagos\Service\EntityService.
     */
    protected function getEntityService()
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
     * @access protected
     *
     * @return HTTPStatus Object containing the result of the operation.
     */
    protected function updateValidateAndPersist(Entity $entity, array $updates, $action)
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
                    'A %s has been successfully %sd with an ID of %d.',
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

    public function validateProperty($entityName)
    {
        $response = $this->slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $this->setQuitOnFinalize(true);

        $params = $this->slim->request->params();

        if (count($params) == 0) {
            print json_encode('Property to be validated not supplied');
            return;
        }

        if (count($params) > 1) {
            print json_encode('Validation of multiple properties not allowed.');
            return;
        }

        $paramName = array_keys($params);

        if (!property_exists("\Pelagos\Entity\\$entityName", $paramName[0])) {
            print json_encode("The parameter $paramName[0] is not a valid property of $entityName.");
            return;
        }

        $entityClass = "\Pelagos\Entity\\$entityName";
        $entity = new $entityClass;
        $entity->update($params);
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validateProperty($entity, $paramName[0]);
        if (count($violations) > 0) {
            $violationMsgs = array();
            foreach ($violations as $violation) {
                $violationMsgs[] = $violation->getMessage();
            }
            print json_encode(join($violationMsgs, ', '));
            return;
        }
        print json_encode(true);
    }

    public function handlePost($entityName)
    {
        $response = $this->slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $this->setQuitOnFinalize(true);

        // Check to see that user is logged in.
        // THIS IS AN INSUFFICIENT SECURITY CHECK, THIS WILL
        // HAVE TO BE TIED TO SOME SORT OF ACCESS LIST WHEN
        // RELEASED.
        if (!$this->userIsLoggedIn()) {
            $status = new HTTPStatus(401, 'Login Required to use this feature');
            $response->status($status->getCode());
            $response->body(json_encode($status));
            return;
        }

        // build the fully qualified class name
        $entityClass = "\Pelagos\Entity\\$entityName";
        // instantiate a new entity
        $entity = new $entityClass;
        // get updates from request parameters
        $updates = $this->slim->request->params();
        // set creator to currently logged in user
        $updates['creator'] = $this->getLoggedInUser();
        // set modifier to currently logged in user
        $updates['modifier'] = $this->getLoggedInUser();
        // add any files to updates
        foreach ($_FILES as $fileProperty => $file) {
            if (array_key_exists('tmp_name', $file) and is_file($file['tmp_name'])) {
                $updates[$fileProperty] = file_get_contents($file['tmp_name']);
            }
        }
        $status = $this->updateValidateAndPersist($entity, $updates, 'create');
        if ($status->getCode() == 201) {
            $response->headers->set('Location', $this->getUri() . "/$entityName/" . $entity->getId());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }

    public function handleGet($entityName, $id)
    {
        $response = $this->slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $this->setQuitOnFinalize(true);
        try {
            $entityService = new EntityService($this->getEntityManager());
            $entity = $entityService->get($entityName, $id);
            $status = new HTTPStatus(200, "Found $entityName with id: $id", $entity);
        } catch (ArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
        } catch (PersistenceException $e) {
            $databaseErrorMessage = $e->getDatabaseErrorMessage();
            if (empty($databaseErrorMessage)) {
                $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getMessage());
            } else {
                $status = new HTTPStatus(500, "A database error has occured: $databaseErrorMessage");
            }
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }

    public function handlePut($entityName, $id)
    {
        $response = $this->slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $this->setQuitOnFinalize(true);

        // Check to see that user is logged in.
        // THIS IS AN INSUFFICIENT SECURITY CHECK, THIS WILL
        // HAVE TO BE TIED TO SOME SORT OF ACCESS LIST WHEN
        // RELEASED.
        if (!$this->userIsLoggedIn()) {
            $status = new HTTPStatus(401, 'Login Required to use this feature');
            $response->status($status->getCode());
            $response->body(json_encode($status));
            return;
        }

        try {
            // retrieve the entity
            $entity = $this->getEntityService()->get($entityName, $id);
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
            $response->status($status->getCode());
            $response->body(json_encode($status));
            return;
        }
        // get updates from request parameters
        $updates = $this->slim->request->params();
        // set the modified to the currently logged in user
        $updates['modifier'] = $this->getLoggedInUser();

        // Get the multipart boundary from the Content-Type header
        if (preg_match(
            '/^multipart\/form-data; boundary=(.*)/',
            $this->slim->request->headers->get('Content-Type'),
            $matches
        )) {
            $boundary = $matches[1];

            // Split the body in to parts by slicing on the boundary
            $parts = array_slice(explode($boundary, $this->slim->request()->getBody()), 1);

            foreach ($parts as $part) {
                // If this is the last part, break
                if ($part == "--\r\n") {
                    break;
                }

                // Separate headers and body
                $part = ltrim($part, "\r\n");
                list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

                // Parse the headers
                $raw_headers = explode("\r\n", $raw_headers);
                $headers = array();
                foreach ($raw_headers as $header) {
                    list($name, $value) = explode(':', $header);
                    $headers[strtolower($name)] = ltrim($value, ' ');
                }

                // Parse the Content-Disposition to determine if this is a file part and get the field name
                if (isset($headers['content-disposition'])) {
                    if (preg_match(
                        '/^.+; *name="([^"]+)"; *filename="[^"]+"/',
                        $headers['content-disposition'],
                        $matches
                    )) {
                        $updates[$matches[1]] = $body;
                    }
                }
            }
        }

        $status = $this->updateValidateAndPersist($entity, $updates, 'update');
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }

    public function handleGetAll($entityName)
    {
        $response = $this->slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $this->setQuitOnFinalize(true);
        try {
            $entities = $this->getEntityService()->getAll($entityName);
            $entitiesCount = count($entities);
            $status = new HTTPStatus(
                200,
                "Retrieved $entitiesCount entities of type $entityName",
                $entities
            );
        } catch (PersistenceException $e) {
            $databaseErrorMessage = $e->getDatabaseErrorMessage();
            if (empty($databaseErrorMessage)) {
                $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getMessage());
            } else {
                $status = new HTTPStatus(500, "A database error has occured: $databaseErrorMessage");
            }
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
}
