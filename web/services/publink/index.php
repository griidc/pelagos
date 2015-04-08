<?php

$comp = new \Pelagos\Component();

$comp->slim->get('/', function () {
    $GLOBALS['pelagos']['title'] = 'Publink Service';
    print 'This service creates and removes associations between datasets to publications.';
});

$comp->slim->put('/removelink/:udi/:doiShoulder/:doiBody(/)', function ($udi, $doiShoulder, $doiBody) use ($comp) {
    $doi = $doiShoulder.'/'.$doiBody;
    $Publink = new \Pelagos\Publink;
    $Publink->delink($udi,$doi);
    $comp->quit();
});


$comp->slim->run();

