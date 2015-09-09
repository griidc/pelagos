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
        '/static/js/common.js',
        'static/js/entityForm.js',
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

# add custom Twig extensions
$app->view->parserExtensions = array(
    new \Pelagos\TwigEntityExtensions()
);

// Set the default condition for the entityType parameter to match a camel-case word.
\Slim\Route::setDefaultConditions(
    array(
        'entityType' => '([A-Z][a-z]*)+'
    )
);

$app->get(
    '/:entityType(/)(:entityId)',
    function ($entityType, $entityId = null) use ($app, $comp) {
        if (preg_match_all('/([A-Z][a-z]*)/', $entityType, $entityName)) {
            if (isset($entityId)) {
                $comp->setTitle(implode(' ', $entityName[1]) . ' Landing Page');
            } else {
                $comp->setTitle('Create ' . implode(' ', $entityName[1]));
            }
        }
        if (file_exists("static/js/$entityType" . 'Land.js')) {
            $comp->addJS("static/js/$entityType" . 'Land.js');
        }
        $twigData = array(
            'userLoggedIn' => ($comp->userIsLoggedIn()) ? 'true' : 'false',
        );
        if (isset($entityId)) {
            $entityService = new EntityService($comp->getEntityManager());
            $entity = $entityService->get($entityType, $entityId);
            $twigData[$entityType] = $entity;
        }
        $app->render($entityType . 'Land.html', $twigData);
    }
);

$app->run();

$comp->finalize();
