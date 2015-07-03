<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Person List');

$comp->addJS(
    array(
        '//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js',
        'static/js/personList.js'
    )
);

$comp->addCSS(
    array(
        '//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css'
    )
);

$comp->setJSGlobals();

$twig = new Twig_Environment(new Twig_Loader_Filesystem('./templates'));

// get all Persons and put them in the Twig data array
$twigData = array(
    'persons' => $comp
                    ->getEntityManager()
                    ->getRepository('Pelagos\Entity\Person')
                    ->findAll()
);

echo $twig->render('html/index.html', $twigData);

$comp->finalize();
