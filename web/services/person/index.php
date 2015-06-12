<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use \Pelagos\HTTPStatus;
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
            $person = $comp->createPerson(
                $slim->request->post('firstName'),
                $slim->request->post('lastName'),
                $slim->request->post('emailAddress')
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
        } catch (EmptyRequiredArgumentException $e) {
            $status = new HTTPStatus(400, 'Cannot create person because ' . $e->getMessage() . '.');
        } catch (InvalidFormatArgumentException $e) {
            $status = new HTTPStatus(
                400,
                'Cannot create person because ' . $e->getMessage() .
                '. It should follow this format: ' . $e->getExpectedFormat()
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
