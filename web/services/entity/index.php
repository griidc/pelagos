<?php

require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/lib/EntityWebService.php';

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

$comp = new \Pelagos\Component\EntityWebService;

$slim = new \Slim\Slim;

\Slim\Route::setDefaultConditions(
    array(
        'entityName' => '([A-Z][a-z]*)+'
    )
);

$slim->get(
    '/',
    function () use ($slim) {
        $GLOBALS['pelagos']['title'] = 'Entity Web Service';
        return $slim->render('html/index.html');
    }
);

$slim->get(
    '/:entityName/validateProperty/',
    function ($entityName) use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);

        $params = $slim->request->params();

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
);

$slim->post(
    '/:entityName/',
    function ($entityName) use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);

        // Check to see that user is logged in.
        // THIS IS AN INSUFFICIENT SECURITY CHECK, THIS WILL
        // HAVE TO BE TIED TO SOME SORT OF ACCESS LIST WHEN
        // RELEASED.
        if (!$comp->userIsLoggedIn()) {
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
        $updates = $slim->request->params();
        // set creator to currently logged in user
        $updates['creator'] = $comp->getLoggedInUser();
        // set modifier to currently logged in user
        $updates['modifier'] = $comp->getLoggedInUser();
        // add any files to updates
        foreach ($_FILES as $fileProperty => $file) {
            if (array_key_exists('tmp_name', $file) and is_file($file['tmp_name'])) {
                $updates[$fileProperty] = file_get_contents($file['tmp_name']);
            }
        }
        $status = $comp->updateValidateAndPersist($entity, $updates, 'create');
        if ($status->getCode() == 201) {
            $response->headers->set('Location', $comp->getUri() . "/$entityName/" . $entity->getId());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->get(
    '/:entityName/:id',
    function ($entityName, $id) use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);
        try {
            $entityService = new EntityService($comp->getEntityManager());
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
);

$slim->put(
    '/:entityName/:id',
    function ($entityName, $id) use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);

        // Check to see that user is logged in.
        // THIS IS AN INSUFFICIENT SECURITY CHECK, THIS WILL
        // HAVE TO BE TIED TO SOME SORT OF ACCESS LIST WHEN
        // RELEASED.
        if (!$comp->userIsLoggedIn()) {
            $status = new HTTPStatus(401, 'Login Required to use this feature');
            $response->status($status->getCode());
            $response->body(json_encode($status));
            return;
        }

        try {
            // retrieve the entity
            $entity = $comp->getEntityService->get($entityName, $id);
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
            $response->status($status->getCode());
            $response->body(json_encode($status));
            return;
        }
        // get updates from request parameters
        $updates = $slim->request->params();
        // set the modified to the currently logged in user
        $updates['modifier'] = $comp->getLoggedInUser();
        // add any files to updates
        foreach ($_FILES as $fileProperty => $file) {
            if (array_key_exists('tmp_name', $file) and is_file($file['tmp_name'])) {
                $updates[$fileProperty] = file_get_contents($file['tmp_name']);
            }
        }
        $status = $comp->updateValidateAndPersist($entity, $updates, 'update');
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->get(
    '/:entityName',
    function ($entityName) use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);
        try {
            $entityService = new EntityService($comp->getEntityManager());
            $entities = $entityService->getAll($entityName);
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
);

$slim->run();
$comp->finalize();
