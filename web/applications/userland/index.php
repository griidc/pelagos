<?php
$base_path = $GLOBALS['pelagos']['base_path'];
$component_path = $GLOBALS['pelagos']['component_path'];

require_once __DIR__ . '/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

// drupal_add_js(
    // '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',
    // array('type'=>'external')
// );

drupal_add_js(
    '//cdnjs.cloudflare.com/ajax/libs/jquery-hashchange/v1.3/jquery.ba-hashchange.min.js',
    array('type'=>'external')
);

drupal_add_js('/pelagos/dev/mvde/static/js/pelagosForm.js',
    array('type'=>'external')
);


//$comp->addJS('static/js/pelagosForm.js');
$comp->addJS('static/js/userLand.js');
$comp->addCSS('static/css/userland.css');

drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.tabs');

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader, array('autoescape' => false));

$twigdata = array('base_path' => $base_path);

echo $twig->render('userland.html', $twigdata);
