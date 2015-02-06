<?php

$basePath = $GLOBALS['pelagos']['base_path'];
$rootPath = $GLOBALS['pelagos']['root'];

if (isset($_GET["getForm"]))
{
    
    require_once 'Twig/Autoloader.php';
    
    $twig;
    $twigloader;
    
    Twig_Autoloader::register();
    
    $twigloader = new Twig_Loader_Filesystem('.'); //Change to where templates are kept.
    $twig = new Twig_Environment($twigloader, array('autoescape' => false));
    
    $twigdata = array(); // Twig array, nothing for now.
    
    echo $twig->render('journalForm.html', $twigdata);
    
    exit;
}

drupal_add_js($GLOBALS['pelagos']['base_path'].'/static/js/formHandler.js',array('type'=>'external'));
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.11.1/jquery.validate.min.js',array('type'=>'external'));
drupal_add_library('system', 'ui.widget');
drupal_add_library('system', 'ui.dialog');

drupal_add_js($GLOBALS['pelagos']['base_path'].'/modules/add-journal/static/js/journalForm.js',array('type'=>'external'));

include 'journalPost.php';
include_once $GLOBALS['pelagos']['root'].'/share/php/FormHandler.php';

$myHandler = new FormHandler();

$myHandler->handleForm();

?>
<!--
<html>
<head>
    <title>Journal Test</title>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/spin.js/2.0.1/spin.min.js"></script>

    <script type="text/javascript" src="formHandler.js"></script>

    <script type="text/javascript" src="static/js/journalForm.js"></script>
    </head>
<body>
</body>
-->

<div style="width:600px;heigth:200px;" id="journalForm"></div>

