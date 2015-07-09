<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use \Pelagos\HTTPStatus;
use \Pelagos\Entity\FundingOrganization;
use \Symfony\Component\Validator\Validation;
use \Pelagos\Exception\ArgumentException;
use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Pelagos\Exception\MissingRequiredFieldPersistenceException;
use \Pelagos\Exception\RecordExistsPersistenceException;
use \Pelagos\Exception\RecordNotFoundPersistenceException;
use \Pelagos\Exception\PersistenceException;

$comp = new \Pelagos\Component\EntityService();

$slim = new \Slim\Slim();

$slim->get(
    '/',
    function () use ($slim) {
        $GLOBALS['pelagos']['title'] = 'Funding Organization Web Service';
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
            $fundingOrganization = new FundingOrganization();
            $updates = $slim->request->params();
            $fundingOrganization = $comp->persist(
                $comp->validate(
                    $fundingOrganization->update($updates),
                    Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator()
                )
            );
            $status = new HTTPStatus(
                201,
                sprintf(
                    'A funding organization has been successfully created: %s with at ID of %d.',
                    $fundingOrganization->getName(),
                    $fundingOrganization->getId()
                )
            );
            $response->headers->set('Location', $comp->getUri() . '/' . $fundingOrganization->getId());
        } catch (ValidationException $e) {
            $violations = array();
            foreach ($e->getViolations() as $violation) {
                $violations[] = $violation->getMessage();
            }
            $status = new HTTPStatus(
                400,
                'Cannot create funding organization because: ' . join(', ', $violations)
            );
        } catch (MissingRequiredFieldPersistenceException $e) {
            $status = new HTTPStatus(400, 'Cannot create funding organization because a required field is missing.');
        } catch (RecordExistsPersistenceException $e) {
            $status = new HTTPStatus(409, 'Cannot create funding organization: ' . $e->getDatabaseErrorMessage());
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
