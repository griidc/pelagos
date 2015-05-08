<?php

require_once $GLOBALS['pelagos']['root'] . '/vendor/autoload.php';
require_once "../../../bootstrap.php";

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
    function ($firstName, $lastName, $email) use ($comp) {

        global $quit;
        $quit = true;

        global $user;
        $quit = true;

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
        $entityManager->flush();

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

