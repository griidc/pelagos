<?php

require_once __DIR__.'/../../../vendor/autoload.php';

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

$comp = new \Pelagos\Component;

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

        try {
            $entityClass = "\Pelagos\Entity\\$entityName";
            $entity = new $entityClass;
            if (array_key_exists('logo', $_FILES) and
                array_key_exists('tmp_name', $_FILES['logo']) and
                is_file($_FILES['logo']['tmp_name'])) {
                $entity->setLogo(
                    file_get_contents($_FILES['logo']['tmp_name'])
                );
            }
            $updates = $slim->request->params();
            $updates['creator'] = $comp->getLoggedInUser();
            $updates['modifier'] = $comp->getLoggedInUser();
            if (array_key_exists('logo', $updates)) {
                unset($updates['logo']);
            }
            foreach ($updates as $property => $value) {
                if (empty($value)) {
                    $updates[$property] = null;
                }
            }
            $entityService = new EntityService($comp->getEntityManager());
            $entity = $entityService->persist(
                $entityService->validate(
                    $entity->update($updates),
                    Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator()
                )
            );
            $status = new HTTPStatus(
                201,
                sprintf(
                    'A %s has been successfully created with at ID of %d.',
                    $entityName,
                    $entity->getId()
                )
            );
            $response->headers->set('Location', $comp->getUri() . '/' . $entity->getId());
        } catch (ValidationException $e) {
            $violations = array();
            foreach ($e->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $status = new HTTPStatus(
                400,
                "Cannot create $entityName because: " . join(', ', $violations)
            );
        } catch (MissingRequiredFieldPersistenceException $e) {
            $status = new HTTPStatus(400, "Cannot create $entityName because a required field is missing.");
        } catch (RecordExistsPersistenceException $e) {
            $status = new HTTPStatus(409, "Cannot create $entityName: " . $e->getDatabaseErrorMessage());
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
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
            $updates = $slim->request->params();
            $updates['modifier'] = $comp->getLoggedInUser();

            // get the Funding Organization (F.O.)
            $entityService = new EntityService($comp->getEntityManager());
            $entity = $entityService->get($entityName, $id);

            // handle logo file upload, if set
            if (array_key_exists('logo', $_FILES) and
                array_key_exists('tmp_name', $_FILES['logo']) and
                is_file($_FILES['logo']['tmp_name'])) {
                $updates['logo'] = file_get_contents($_FILES['logo']['tmp_name']);
            }

            foreach ($updates as $property => $value) {
                if (empty($value)) {
                    $updates[$property] = null;
                }
            }

            $entity = $entityService->persist(
                $entityService->validate(
                    $entity->update($updates),
                    Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator()
                )
            );
            $status = new HTTPStatus(200, "Updated $entityName with id: $id", $entity);
        } catch (ArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (ValidationException $e) {
            $violations = array();
            foreach ($e->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $status = new HTTPStatus(
                400,
                "Cannot update $entityName because: " . join(', ', $violations)
            );
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured:: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->run();
$comp->finalize();
