<?php
/**
 * Funding Organization Interface
 *
 * Based on the Funding Org class, a simple web form that creates a funding source
 * then sends it to the web-service to save.
 *
 */

require_once __DIR__.'/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Entity Creator');

$comp->setJSGlobals();

$comp->addJS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
        'static/js/entityCreator.js',
    )
);

$comp->addLibrary('ui.widget');
$comp->addLibrary('ui.dialog');

$app = new \Slim\Slim(array(
        'view' => new \Slim\Views\Twig()
));

$app->get('/:entity', function ($entity) use ($app) {
   
    $app->render("$entity.html");
});

$app->run();

$comp->finalize();
