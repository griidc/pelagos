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

$comp->setTitle('Funding Organization Creator');

$comp->addJS(
    array(
        '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
        'static/js/fundingOrg.js',
    )
);

$comp->addLibrary('ui.widget');
$comp->addLibrary('ui.dialog');

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader, array('autoescape' => false));

$twigdata = array('base_path' => $comp->getBasePath());

echo $twig->render('fundingOrgForm.html', $twigdata);

$comp->finalize();
