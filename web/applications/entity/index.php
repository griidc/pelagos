<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Pelagos\Factory\EntityManagerFactory;
use Pelagos\Service\EntityService;

$app = new \Slim\Slim(
    array(
            'view' => new \Slim\Views\Twig()
         )
);

// Add custom Twig extensions.
$app->view->parserExtensions = array(
    new \Pelagos\TwigEntityExtensions()
);

// Set the default condition for the entityType parameter to match a camel-case word.
\Slim\Route::setDefaultConditions(
    array(
        'entityType' => '([A-Z][a-z]*)+'
    )
);

$entityService = new EntityService(EntityManagerFactory::create());

$comp = null;

$app->get(
    '/:entityType(/)(:entityId)',
    function ($entityType, $entityId = null) use ($app, $entityService, &$comp) {
        $appClass = "\Pelagos\Component\EntityApplication\\$entityType" . 'Application';

        if (class_exists($appClass)) {
            $comp = new $appClass($app, $entityService);
        } else {
            $comp = new \Pelagos\Component\EntityApplication($app, $entityService);
        }

        if (isset($entityId)) {
            $comp->handleEntityInstance($entityType, $entityId);
        } else {
            $comp->handleEntity($entityType);
        }
    }
);

$app->post(
    '/:entityType(/)(:entityId)',
    function ($entityType, $entityId = null) use ($app, $entityService, &$comp) {
        $appClass = "\Pelagos\Component\EntityApplication\\$entityType" . 'Application';

        if (class_exists($appClass)) {
            $comp = new $appClass($app, $entityService);
        } else {
            $comp = new \Pelagos\Component\EntityApplication($app, $entityService);
        }

        $comp->handlePost($entityType, $entityId);
    }
);

$app->run();

if (isset($comp)) {
    $comp->finalize();
}
