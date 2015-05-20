<?php
/**
 * Person Interface
 *
 * Based on the person class, a simple web form that gets First, Last Name and e-mail,
 * then sends it to the web-service.
 *
 */

$base_path = $GLOBALS['pelagos']['base_path'];
$component_path = $GLOBALS['pelagos']['component_path'];

drupal_add_js(
    '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
    array('type'=>'external')
);

drupal_add_js($component_path.'/static/js/personForm.js', 'external');

drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.dialog');

require_once 'Twig/Autoloader.php';

global $twig;
$twigloader;

Twig_Autoloader::register();

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader, array('autoescape' => false));

$twigdata = array('base_path' => $base_path);

echo $twig->render('personForm.html', $twigdata);
