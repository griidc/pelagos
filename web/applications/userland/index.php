<?php
$base_path = $GLOBALS['pelagos']['base_path'];
$component_path = $GLOBALS['pelagos']['component_path'];

drupal_add_js(
    '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
    array('type'=>'external')
);

drupal_add_js(
    '//cdnjs.cloudflare.com/ajax/libs/jquery-hashchange/v1.3/jquery.ba-hashchange.min.js',
    array('type'=>'external')
);

drupal_add_js(
    '/pelagos/dev/mvde/static/js/pelagosForm.js',
    array('type'=>'external')
);



drupal_add_js($component_path.'/static/js/userLand.js', 'external');
drupal_add_css($component_path.'/static/css/userland.css', array('type'=>'external'));

drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.tabs');

require_once 'Twig/Autoloader.php';

global $twig;
$twigloader;

Twig_Autoloader::register();

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader, array('autoescape' => false));

$twigdata = array('base_path' => $base_path);

echo $twig->render('userland.html', $twigdata);
