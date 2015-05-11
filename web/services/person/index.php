<?php

// currently set by Drupal to local pelagos root
require_once $GLOBALS['pelagos']['root'] . '/bootstrap.php';

$comp = new \Pelagos\Component();

global $quit;
$quit = false;

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
            $HTTPStatus = new \Pelagos\HTTPStatus(400, 'Improperly formatted email address');
            $comp->slim->response->headers->set('Content-Type', 'application/json');
            $comp->slim->response->status($HTTPStatus->code);
            $comp->slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        if (!isset($user->name)) {
            $quit = true;
            $HTTPStatus = new \Pelagos\HTTPStatus(401, 'Login Required to use this feature.');
            $comp->slim->response->headers->set('Content-Type', 'application/json');
            $comp->slim->response->status($HTTPStatus->code);
            $comp->slim->response->setBody($HTTPStatus->asJSON());
            return;
        }

        $person = new \Pelagos\Entity\Person($firstName, $lastName, $email);

        $entityManager->persist($person);

        try {
            $entityManager->flush();

        } catch (\Exception $error) {
            print $error->getMessage();
            return;
        }

        $cn = $person->getFirstName() . ' ' . $person->getLastName();
        $id = $person->getId();
        print "Hello $cn.  You have been assigned ID: $id.";

        return;
    }
)->via('PUT','GET');


$comp->slim->run();

if ($quit) {
    $comp->quit();
}

