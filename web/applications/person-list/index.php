<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$comp = new \Pelagos\Component;

$comp->setTitle('Person List');

$twig = new Twig_Environment(new Twig_Loader_Filesystem('./templates'));

// get all Persons ordered by modificationTimeStamp descending and put them in the Twig data array
$twigData = array(
    'persons' => $comp
                    ->getEntityManager()
                    ->getRepository('Pelagos\Entity\Person')
                    ->findBy(array(), array('modificationTimeStamp' => 'DESC'))
);

echo $twig->render('html/index.html', $twigData);

$comp->finalize();
