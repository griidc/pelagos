<?php

// currently set by Drupal to local pelagos root
require_once __DIR__.'/../../../bootstrap.php';
require_once "lib/PersonService.php";

$comp = new \Pelagos\Component\PersonService();

$comp->slim->get(
    '/',
    function () use ($comp) {
        $comp->giveDescription();
    }
);

$comp->slim->post(
    '/',
    function () use ($comp, $entityManager) {
        $firstName = $comp->slim->request->post('firstName');
        $lastName = $comp->slim->request->post('lastName');
        $emailAddress = $comp->slim->request->post('emailAddress');
        $comp->createPerson($entityManager, $firstName, $lastName, $emailAddress);
    }
);

$comp->slim->run();
$comp->finalize();
