<?php
/**
 * Generic Entity View Interface
 *
 * Shows a list a type Entity
 *
 */

require_once __DIR__.'/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Entity Creator');

$comp->setJSGlobals();

$comp->addJS(
    array(
        '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js',
        'static/js/entityList.js'
    )
);

$comp->addCSS(
    array(
        '//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css'
    )
);

$comp->addLibrary('ui.dialog');

$app = new \Slim\Slim(array(
        'view' => new \Slim\Views\Twig()
));

$app->get('/:entity', function ($entity) use ($app,$comp) {
    if (file_exists(__DIR__."/static/js/$entity.js")) {
        $comp->addJS("static/js/$entity.js");
    }
    $twigData = array(
        'entityType' => $entity
    );
    $app->render("entityList.html", $twigData);

});

$app->run();

$comp->finalize();
