<?php

$comp = new \Pelagos\Component();

$comp->slim->get('/', function () {
    $GLOBALS['pelagos']['title'] = 'Citation Service';
    print 'This is a citation service...';
});

$comp->slim->get('/publication(/)', function () use ($comp) {
    header('Content-Type:application/json');
    $status = new \Pelagos\HTTPStatus(400, 'No DOI provided.');
    http_response_code($status->code);
    print $status->asJSON();
    $comp->quit();
});

$comp->slim->get('/publication/:doiShoulder(/(:doiBody))', function ($doiShoulder, $doiBody = '') use ($comp) {
    header('Content-Type:application/json');
    $pub = new \Pelagos\Publication("$doiShoulder/$doiBody");
    $citation = $pub->getCitation();
    if ($citation === null) {
        $status = $pub->pullCitation('apa');
        if ($status->code == 200) {
            print $pub->getCitation()->asJSON();
        } else {
            http_response_code($status->code);
            print $status->asJSON();
        }
        $comp->quit();
    }
    print $citation->asJSON();
    $comp->quit();
});

$comp->slim->run();
