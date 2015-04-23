<?php

$comp = new \Pelagos\Component();

global $quit;
$quit = false;

$comp->slim->get('/', function () use ($comp) {
    $GLOBALS['pelagos']['title'] = 'Publication-Dataset Linker';
    $comp->addLibrary('ui.button');
    $comp->addJS('static/js/publink.js');
    $comp->addCSS('static/css/publink.css');
    # $comp->addJS currently only supports local js files
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/spin.js/2.0.1/spin.min.js');
    $stash = array('pelagos_base_path' => $GLOBALS['pelagos']['base_path']);
    return $comp->slim->render('html/index.html', $stash);
});

$comp->slim->get('/GetLinks(/)', function () use ($comp) {
    drupal_add_js('//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js');
    drupal_add_css('//cdn.datatables.net/1.10.6/css/jquery.dataTables.min.css');
    $comp->addCSS('static/css/linkList.css');
    $comp->addLibrary('ui.button');
    $comp->addJS('static/js/linkList.js');
    $stash = array('pelagos_base_path' => $GLOBALS['pelagos']['base_path']);
    $stash = array('pelagos_component_path' => $GLOBALS['pelagos']['component_path']);
    return $comp->slim->render('html/linkList.html', $stash);
});

$comp->slim->get('/GetLinksJSON(/)', function () use ($comp) {
    global $quit;
    require_once "../../services/plinker/lib/Storage.php";
    $storage = new \Pelagos\Storage;
    $linksArray = $storage->getAll("Publink");
    foreach ($linksArray as $link) {
        list($fc, $proj) = getFcAndProj($link['udi']);
        $inside[] = array(
                        'del'       => "",
                        'udi'       => $link['udi'],
                        'fc'        => $fc,
                        'proj'      => $proj,
                        'doi'       => $link['doi'],
                        'username'  => $link['username'],
                        'created'   => $link['created']
                  );
    }
    $data['aaData'] = $inside;
    echo json_encode($data);
    $quit = true;
});

$comp->slim->run();

if ($quit) {
    $comp->quit();
}

function getFcAndProj($key) {
    # once we change the UDI format, this will break.  At this point,
    # this will have to be replaced by a database/persistence lookup
    # for these details.
    list($fc, $proj, $task) = preg_split('/\./', $key);
    return array($fc, $proj);
}
