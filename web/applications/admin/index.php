<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Admin Page');

$comp->setJSGlobals();

$comp->addJS(
    array(
     
    )
);

$comp->addCSS(
    array(
        'static/css/admin.css',
    )
);

$comp->addLibrary(
    array(
        'ui.dialog',
        'ui.tabs',
        'ui.widget'
    )
);

$app = new \Slim\Slim(
    array(
        'view' => new \Slim\Views\Twig()
    )
);

$twigData = array(
    'basePath' => $comp->getBasePath()
);

$app->render($entityType . 'adminMain.html', $twigData);

$comp->finalize();
