<?php

$comp = new \Pelagos\Component();

require_once "ldap.php";
require_once "lib/Storage.php";
require_once "lib/Publink.php";

$comp->slim->get('/', function () {
    $GLOBALS['pelagos']['title'] = 'Publink Service';
    print 'This service creates associations between datasets and publications.';
});

$comp->slim->link('/makelink/:udi/:doiShoulder/:doiBody(/)', function ($udi, $doiShoulder, $doiBody) use ($comp) {
    $doi = $doiShoulder.'/'.$doiBody;
    $Publink = new \Pelagos\Publink;
    $Publink->createLink($udi,$doi,getEmployeeNumberFromUID($GLOBALS['_SESSION']['phpCAS']['user']));
    $comp->quit();
});

$comp->slim->run();
