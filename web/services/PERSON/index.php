<?php

require_once $GLOBALS['pelagos']['root'] . '/vendor/autoload.php';

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

        $person = new \Pelagos\Person($firstName, $lastName, $email);
        $cn = $person->getFirstName() . ' ' . $person->getLastName();
        print "Hello $cn";
        return;
    }
)->via('PUT','GET');


$comp->slim->run();

if ($quit) {
    $comp->quit();
}

