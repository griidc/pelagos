<?php

$path = "/opt/pelagos/share/php/Pelagos";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

$comp = new \Pelagos\Component();

global $quit;
$quit = false;

$comp->slim->get(
    '/',
    function () use ($comp) {
        $GLOBALS['pelagos']['title'] = 'Person Webservice';
        $stash = array('pelagos_base_path' => $GLOBALS['pelagos']['base_path']);
        return $comp->slim->render('html/index.html', $stash);
    }
);
