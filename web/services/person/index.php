<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use \Pelagos\HTTPStatus;
use \Pelagos\Entity\Person;
use \Symfony\Component\Validator\Validation;
use \Pelagos\Exception\ValidationException;
use \Pelagos\Exception\ArgumentException;
use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Pelagos\Exception\MissingRequiredFieldPersistenceException;
use \Pelagos\Exception\RecordExistsPersistenceException;
use \Pelagos\Exception\RecordNotFoundPersistenceException;
use \Pelagos\Exception\PersistenceException;

$comp = new \Pelagos\Component\PersonService();

$slim = new \Slim\Slim();

$slim->get(
    '/',
    function () use ($slim) {
        $GLOBALS['pelagos']['title'] = 'Person Web Service';
        return $slim->render('html/index.html');
    }
);

$slim->get(
    '/validateProperty/',
    function () use ($comp, $slim) {
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
        if (property_exists('\Pelagos\Entity\Person', $paramName[0])) {
            $person = new Person;
            $person->update($params);
            $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
            $violations = $validator->validateProperty($person, $paramName[0]);
            if (count($violations) > 0) {
                $violationMsgs = array();
                foreach ($violations as $violation) {
                    $violationMsgs[] = $violation->getMessage();
                }
                print json_encode(join($violationMsgs, ', '));
            } else {
                print json_encode(true);
            }
        } else {
            print json_encode("The parameter $paramName[0] is not a valid property of Person.");
        }
    }
);

$slim->post(
    '/',
    function () use ($comp, $slim) {
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
            $person = new Person();
            $person = $comp->persist(
                $comp->validate(
                    $person->update($slim->request->params()),
                    Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator()
                )
            );
            $status = new HTTPStatus(
                201,
                sprintf(
                    'A person has been successfully created: %s %s (%s) with at ID of %d.',
                    $person->getFirstName(),
                    $person->getLastName(),
                    $person->getEmailAddress(),
                    $person->getId()
                )
            );
            $response->headers->set('Location', $comp->getUri() . '/' . $person->getId());
        } catch (ValidationException $e) {
            $violations = array();
            foreach ($e->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $status = new HTTPStatus(
                400,
                'Cannot create person because: ' . join(', ', $violations)
            );
        } catch (MissingRequiredFieldPersistenceException $e) {
            $status = new HTTPStatus(400, 'Cannot create person because a required field is missing.');
        } catch (RecordExistsPersistenceException $e) {
            $status = new HTTPStatus(409, 'Cannot create person: ' . $e->getDatabaseErrorMessage());
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
    '/:id',
    function ($id) use ($comp, $slim) {
        $response = $slim->response;
        $response->headers->set('Content-Type', 'application/json');
        $comp->setQuitOnFinalize(true);
        try {
            $person = $comp->getPerson($id);
            $status = new HTTPStatus(200, "Found Person with id: $id", $person);
        } catch (ArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->put(
    '/:id',
    function ($id) use ($comp, $slim) {
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

        $personProps = array(
            'firstName',
            'lastName',
            'emailAddress',
        );
        try {
            $updates = array();
            foreach ($personProps as $prop) {
                if ($slim->request->put($prop) !== null) {
                    $updates[$prop] = $slim->request->put($prop);
                }
            }
            $person = $comp->updatePerson($id, $updates);
            $status = new HTTPStatus(200, "Updated Person with id: $id", $person);
        } catch (ArgumentException $e) {
            $status = new HTTPStatus(400, $e->getMessage());
        } catch (RecordNotFoundPersistenceException $e) {
            $status = new HTTPStatus(404, $e->getMessage());
        } catch (PersistenceException $e) {
            $status = new HTTPStatus(500, 'A database error has occured: ' . $e->getDatabaseErrorMessage());
        } catch (\Exception $e) {
            $status = new HTTPStatus(500, 'A general error has occured: ' . $e->getMessage());
        }
        $response->status($status->getCode());
        $response->body(json_encode($status));
    }
);

$slim->run();
$comp->finalize();
