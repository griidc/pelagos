<?php 

drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js', array('type'=>'external'));

drupal_add_js('/pelagos/dev/mvde/applications/person-editor/static/js/personForm.js', array('type'=>'external'));

drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');
drupal_add_library('system', 'ui.dialog');

require_once 'Twig/Autoloader.php';

global $twig;
$twigloader;

Twig_Autoloader::register();

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader,array('autoescape' => false));

$twigdata = array();

echo $twig->render('personForm.html', $twigdata);

?>
