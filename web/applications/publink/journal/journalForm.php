<?php
    require_once 'Twig/Autoloader.php';

    $twig;
    $twigloader;

    Twig_Autoloader::register();

    $twigloader = new Twig_Loader_Filesystem('.'); //Change to where templates are kept.
    $twig = new Twig_Environment($twigloader,array('autoescape' => false));

    $isAdmin = false; //Automate this <<<

    $twigdata = array('isadmin'=>$isAdmin);

    echo $twig->render('journalForm.html', $twigdata); 
?>