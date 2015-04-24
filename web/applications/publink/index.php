<?php

$comp = new \Pelagos\Component();

$comp->slim->get('/', function () use ($comp) {
    $GLOBALS['pelagos']['title'] = 'Dataset-Publication Linker';
    $comp->addLibrary('ui.button');
    $comp->addJS('static/js/publink.js');
    $comp->addCSS('static/css/publink.css');
    # $comp->addJS currently only supports local js files
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/spin.js/2.0.1/spin.min.js');
    $stash = array('pelagos_base_path' => $GLOBALS['pelagos']['base_path']);
    return $comp->slim->render('html/index.html', $stash);
});

$comp->slim->run();
