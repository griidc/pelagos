<?php
/**
 * Person Interface
 *
 * Based on the person class, a simple web form that gets First, Last Name and e-mail,
 * then sends it to the web-service.
 *
 */

require_once __DIR__.'/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Person Editor');

$comp->addJS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
        'static/js/personForm.js',
    )
);

$comp->addLibrary('ui.widget');
$comp->addLibrary('ui.dialog');

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader, array('autoescape' => false));

$twigdata = array('base_path' => $comp->getBasePath());

echo $twig->render('personForm.html', $twigdata);

$comp->finalize();
