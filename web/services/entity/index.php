<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$slim = new \Slim\Slim;
$comp = new \Pelagos\Component\EntityWebService($slim);

// Set the default condition for the entityType parameter to match a camel-case word.
\Slim\Route::setDefaultConditions(
    array(
        'entityType' => '([A-Z][a-z]*)+'
    )
);

// Default GET route that provides documentation as HTML.
$slim->get(
    '/',
    function () use ($slim) {
        $GLOBALS['pelagos']['title'] = 'Entity Web Service';
        return $slim->render('html/index.html');
    }
);

// GET route to validate properties of $entityType.
$slim->get(
    '/:entityType/validateProperty/',
    function ($entityType) use ($comp) {
        $comp->validateProperty($entityType);
    }
);

// POST route for creating a new entity.
$slim->post(
    '/:entityType/',
    function ($entityType) use ($comp) {
        $comp->handlePost($entityType);
    }
);

// GET route for retrieving an entity.
$slim->get(
    '/:entityType/:id',
    function ($entityType, $id) use ($comp) {
        $comp->handleGet($entityType, $id);
    }
);

// PUT route for updating an entity.
$slim->put(
    '/:entityType/:id',
    function ($entityType, $id) use ($comp) {
        $comp->handlePut($entityType, $id);
    }
);

// GET route for retrieveing all entities of a given type.
$slim->get(
    '/:entityType',
    function ($entityType) use ($comp) {
        $comp->handleGetAll($entityType);
    }
);

$slim->run();
$comp->finalize();
