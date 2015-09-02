<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Pelagos\Service\EntityService;

$comp = new \Pelagos\Component;

$comp->setTitle('Funding Organization Landing Page');

$comp->setJSGlobals();

$comp->addJS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery-hashchange/v1.3/jquery.ba-hashchange.min.js',
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

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader, array('autoescape' => false));

$app = new \Slim\Slim(
    array(
            'view' => new \Slim\Views\Twig()
        )
);

$app->get('/:entityId', function ($entityId) use ($app, $comp) {
    $entityService = new EntityService($comp->getEntityManager());
    $entity = $entityService->get('FundingOrganization', $entityId);
    
    //var_dump($entity);
    
    // if (file_exists(__DIR__."/static/js/$entityType.js")) {
        // $comp->addJS("static/js/$entityType.js");
    // }
    // if (file_exists(__DIR__."/static/css/$entityType.css")) {
        // $comp->addCSS("static/css/$entityType.css");
    // }
    
    $twigData = array(
        'userLoggedIn' => ($comp->userIsLoggedIn()) ? 'true' : 'false',
        'entity' => $entity,
    );
    $app->render('fundingOrganizationLand.html', $twigData);
    
});

$app->run();

$comp->finalize();
