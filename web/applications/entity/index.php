<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Pelagos\Service\EntityService;
use Pelagos\Exception\ArgumentException;
use Pelagos\Exception\RecordNotFoundPersistenceException;
use Pelagos\Factory\EntityManagerFactory;

$comp = new \Pelagos\Component;

$comp->setTitle('Entity');

$comp->setJSGlobals();

$comp->addJS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-noty/2.3.5/packaged/jquery.noty.packaged.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js',
        '/static/js/common.js',
        'static/js/entityForm.js',
        'static/js/entity.js',
    )
);

$comp->addCSS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/animate.css/3.3.0/animate.min.css',
        '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css',
        'static/css/entity.css',
    )
);

$comp->addLibrary(
    array(
        'ui.datepicker',
        'ui.dialog',
        'ui.tabs',
        'ui.widget',
        'ui.autocomplete',
    )
);

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
        $entityService = new EntityService(EntityManagerFactory::create());
        $twigData['entityService'] = $entityService;
        if (isset($entityId)) {
            try {
                $entity = $entityService->get($entityType, $entityId);
                $app->response->setStatus(200);
            } catch (ArgumentException $e) {
                $app->response->setStatus(400);
            } catch (RecordNotFoundPersistenceException $e) {
                $app->response->setStatus(404);
            } catch (\Exception $e) {
                $app->response->setStatus(500);
            }
            if ($app->response->getStatus() != 200) {
                $app->render('error.html', array('errorMessage' => $e->getMessage()));
                return;
            }
            $twigData[$entityType] = $entity;
        }
        $app->render($entityType . 'Land.html', $twigData);
    }
);

$app->run();

$comp->finalize();
