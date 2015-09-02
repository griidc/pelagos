<?php

require_once __DIR__.'/../../../vendor/autoload.php';

use Pelagos\Service\EntityService;

$comp = new \Pelagos\Component;

$comp->setTitle('Entity');

$comp->setJSGlobals();

$comp->addJS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-noty/2.3.5/packaged/jquery.noty.packaged.min.js',
        '/static/js/pelagosForm.js',
        'static/js/entity.js',
    )
);

$comp->addCSS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/animate.css/3.3.0/animate.min.css',
        'static/css/entity.css',
    )
);

$comp->addLibrary(
    array(
        'ui.datepicker',
        'ui.dialog',
        'ui.tabs',
        'ui.widget',
    )
);

$app = new \Slim\Slim(
    array(
        'view' => new \Slim\Views\Twig()
    )
);

// Set the default condition for the entityType parameter to match a camel-case word.
\Slim\Route::setDefaultConditions(
    array(
        'entityType' => '([A-Z][a-z]*)+'
    )
);

$app->get(
    '/:entityType',
    function ($entityType) use ($app, $comp) {
        if (preg_match_all('/([A-Z][a-z]*)/', $entityType, $entityName)) {
            $comp->setTitle('Create ' . implode(' ', $entityName[1]));
        }
        $twigData = array(
            'userLoggedIn' => ($comp->userIsLoggedIn()) ? 'true' : 'false',
        );
        $app->render($entityType . 'Land.html', $twigData);
    }
);

$app->get(
    '/:entityType/:entityId',
    function ($entityType, $entityId) use ($app, $comp) {
        if (preg_match_all('/([A-Z][a-z]*)/', $entityType, $entityName)) {
            $comp->setTitle(implode(' ', $entityName[1]) . ' Landing Page');
        }
        $entityService = new EntityService($comp->getEntityManager());
        $entity = $entityService->get($entityType, $entityId);
        $twigData = array(
            'userLoggedIn' => ($comp->userIsLoggedIn()) ? 'true' : 'false',
            'entity' => $entity,
        );
        $app->render($entityType . 'Land.html', $twigData);
    }
);

$app->run();

$comp->finalize();
