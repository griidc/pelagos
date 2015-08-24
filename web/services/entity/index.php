<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$slim = new \Slim\Slim;
$comp = new \Pelagos\Component\EntityWebService($slim);

// Set the default condition for the entityName parameter to match a camel-case word.
\Slim\Route::setDefaultConditions(
    array(
        'entityName' => '([A-Z][a-z]*)+'
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

// GET route to validate properties of $entityName.
$slim->get(
    '/:entityName/validateProperty/',
    function ($entityName) use ($comp) {
        $comp->validateProperty($entityName);
    }
);

// POST route for creating a new entity.
$slim->post(
    '/:entityName/',
    function ($entityName) use ($comp) {
        $comp->handlePost($entityName);
    }
);

// GET route for retrieving an entity.
$slim->get(
    '/:entityName/:id',
    function ($entityName, $id) use ($comp) {
        $comp->handleGet($entityName, $id);
    }
);

// PUT route for updating an entity.
$slim->put(
    '/:entityName/:id',
    function ($entityName, $id) use ($comp) {
        $comp->handlePut($entityName, $id);
    }
);

// GET route for retrieveing all entities of a given type.
$slim->get(
    '/:entityName',
    function ($entityName) use ($comp) {
        $comp->handleGetAll($entityName);
    }
);

$slim->run();
$comp->finalize();
