<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('User Landing Page');

$comp->addJS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery-hashchange/v1.3/jquery.ba-hashchange.min.js',
        '/static/js/pelagosForm.js',
        'static/js/userLand.js',
        '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js'
    )
);

$comp->addCSS('static/css/userland.css');

$comp->addLibrary(array('ui.widget','ui.dialog','ui.tabs'));

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader, array('autoescape' => false));

$twigdata = array('base_path' => $comp->getBasePath());

echo $twig->render('userland.html', $twigdata);

$comp->finalize();
