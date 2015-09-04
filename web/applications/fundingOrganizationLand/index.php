<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Pelagos\Service\EntityService;

$comp = new \Pelagos\Component;

$comp->setTitle('Funding Organization Landing Page');

$comp->setJSGlobals();

$comp->addJS(
    array(
        'static/js/editableForm.js',
        'static/js/fundingOrganizationLand.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-noty/2.3.5/packaged/jquery.noty.packaged.min.js'
    )
);

$comp->addCSS(
    array(
        'static/css/fundingOrganizationLand.css',
        '//cdnjs.cloudflare.com/ajax/libs/animate.css/3.3.0/animate.min.css'
    )
);

$comp->addLibrary(array('ui.widget','ui.dialog','ui.tabs','ui.datepicker'));

$app = new \Slim\Slim(
    array(
            'view' => new \Slim\Views\Twig()
        )
);

$app->get(
    '/:entityId',
    function ($entityId) use ($app, $comp) {
        $entityService = new EntityService($comp->getEntityManager());
        $entity = $entityService->get('FundingOrganization', $entityId);

        $twigData = array(
            'userLoggedIn' => ($comp->userIsLoggedIn()) ? 'true' : 'false',
            'entity' => $entity,
        );
        $app->render('fundingOrganizationLand.html', $twigData);
    }
);

$app->run();

$comp->finalize();
