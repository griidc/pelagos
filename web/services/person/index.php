<?php

// currently set by Drupal to local pelagos root
require_once $GLOBALS['pelagos']['root'] . '/bootstrap.php';

$comp = new \Pelagos\Component();

global $quit;
$quit = false;

$code = '';
$msg = '';

$comp->slim->get(
    '/',
    function () use ($comp) {
        $GLOBALS['pelagos']['title'] = 'Person Webservice';
        $stash = array('pelagos_base_path' => $GLOBALS['pelagos']['base_path']);
        $comp->slim->render("html/index.html");
        return;
    }
);

$comp->slim->map(
    '/:firstName/:lastName/:email(/)',
    function ($firstName, $lastName, $email) use ($comp, $entityManager) {

        global $quit;
        $quit = true;

        global $user;
        $quit = true;

        // First name, last name, and email have to exist, otherwise the route
        // would not have been matched

        // validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $quit = true;
            $code = 400;
            $msg = "Improperly formatted email address";
            $HTTPStatus = new \Pelagos\HTTPStatus($code,$msg);
            $comp->slim->response->headers->set('Content-Type', 'application/json');
            $comp->slim->response->status($HTTPStatus->code);
            $comp->slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        // Check to see that user has been authorized
        if (!isset($user->name)) {
            $quit = true;
            $code = 401;
            $msg = "Login Required to use this feature";
            $HTTPStatus = new \Pelagos\HTTPStatus($code,$msg);
            $comp->slim->response->headers->set('Content-Type', 'application/json');
            $comp->slim->response->status($HTTPStatus->code);
            $comp->slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        $person = new \Pelagos\Entity\Person($firstName, $lastName, $email);

        $entityManager->persist($person);

        try {
            $quit = true;
            $entityManager->flush();
            $firstName = $person->getFirstName();
            $lastName = $person->getLastName();
            $email = $person->getEmailAddress();
            $id = $person->getId();

            $code = 200;
            $msg = "A person has been successfully created $firstName $lastName ($email) with at ID of $id.";
        } catch (\Exception $error) {
            $quit = true;
            // Duplicate Error - 23505
            if (preg_match('/SQLSTATE\[23505\]: Unique violation/', $error->getMessage())){
                $code = 409;
                $msg = 'This record already exists in the database';
            } else {
                $code = 500;
                $msg = "A general database error has occured." . $error->getMessage();
            }
        }

        $HTTPStatus = new \Pelagos\HTTPStatus($code, $msg);
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());

        return;
    }
)->via('PUT','GET');


$comp->slim->run();

if ($quit) {
    $comp->quit();
}

