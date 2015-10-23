<?php
/**
 * Generic Entity View Interface.
 *
 * Shows a list a type Entity
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Entity Creator');

$comp->setJSGlobals();

$comp->addJS(
    array(
        '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js',
        '//cdn.datatables.net/select/1.0.1/js/dataTables.select.min.js',
        '/static/js/common.js',
        'static/js/entityList.js',
    )
);

$comp->addCSS(
    array(
        '//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css'
    )
);

$comp->addLibrary('ui.dialog');

$app = new \Slim\Slim(
    array(
        'view' => new \Slim\Views\Twig()
    )
);

$comp->addJS('var userIsLoggedIn = ' . ($comp->userIsLoggedIn() ? 'true' : 'false') . ';', 'inline');

$app->get(
    '/:entity',
    function ($entity) use ($app, $comp) {
        if (preg_match_all('/([A-Z][a-z]*)/', $entity, $entityName)) {
            $comp->setTitle(implode(' ', $entityName[1]) . ' List');
        }

        if (file_exists(__DIR__ . "/static/js/$entity.js")) {
            $comp->addJS("static/js/$entity.js");
        }
        $twigData = array(
            'entityType' => $entity
        );
        $app->render('entityList.html', $twigData);
    }
);

$app->run();

$comp->finalize();
