<?php

// currently set by Drupal to local pelagos root
require_once $GLOBALS['pelagos']['root'] . '/bootstrap.php';
require_once "lib/PersonService.php";

$comp = new \Pelagos\Component\PersonService();

$comp->slim->get(
    '/',
    function () use ($comp) {
        $comp->giveDescription();
    }
);

$comp->slim->get(
    '/:firstName/:lastName/:email(/)',
    function ($firstName, $lastName, $email) use ($comp, $entityManager) {
        $comp->createPerson($entityManager, $firstName, $lastName, $email);
    }
);

$comp->slim->run();
$comp->finalize();
