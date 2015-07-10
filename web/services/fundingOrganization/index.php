<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use \Pelagos\HTTPStatus;
use \Pelagos\Entity\FundingOrganization;
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

$slim->get(
    '/',
    function () use ($slim) {
        $GLOBALS['pelagos']['title'] = 'Funding Organization Web Service';
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

        if (!property_exists('\Pelagos\Entity\FundingOrganization', $paramName[0])) {
            print json_encode("The parameter $paramName[0] is not a valid property of FundingOrganization.");
            return;
        }

        $fundingOrganization = new FundingOrganization;
        $fundingOrganization->update($params);
        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $violations = $validator->validateProperty($fundingOrganization, $paramName[0]);
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
            if (array_key_exists('logo', $_FILES) and
                array_key_exists('tmp_name', $_FILES['logo']) and
                is_file($_FILES['logo']['tmp_name'])) {
                $fundingOrganization->setLogo(
                    file_get_contents($_FILES['logo']['tmp_name'])
                );
            }
            $updates = $slim->request->params();
            if (array_key_exists('logo', $updates)) {
                unset($updates['logo']);
            }
            $entityService = new EntityService($comp->getEntityManager());
            $fundingOrganization = $entityService->persist(
                $entityService->validate(
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
