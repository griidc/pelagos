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
    drupal_add_js('//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js');
    drupal_add_js('//cdn.datatables.net/1.10.6/js/jquery.dataTables.min.js');
    drupal_add_css('//cdn.datatables.net/1.10.6/css/jquery.dataTables.min.css');
    $comp->addJS('static/js/linkList.js');
    $stash = array('pelagos_base_path' => $GLOBALS['pelagos']['base_path']);
    return $comp->slim->render('html/linkList.html', $stash);
});

$comp->slim->get('/GetLinksJSON(/)', function () use ($comp) {
    global $quit;
    $quit = true;
    require_once "DBUtils.php";
    $sql = "select dataset_udi, publication_doi, username, dataset2publication_createtime
            from dataset2publication_link order by dataset2publication_createtime desc";
    $dbh = openDB("GOMRI_RO");
    $sth = $dbh->prepare($sql);
    $sth->execute();
    $inside = array();
    // using PDO::FETCH_NUM to minimize json object size
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
        $inside[] = array(
                        'del'       => "<button class=\"delBut\" data-udi=\"".$row['dataset_udi']."\" data-doi=\"".$row['publication_doi']."\"  >X</button>",
                        'udi'       => $row['dataset_udi'],
                        'doi'       => $row['publication_doi'],
                        'username'  => $row['username'],
                        'created'   => $row['dataset2publication_createtime']
                  );
    }
    $sth = null;
    $dbh = null;
    $data['aaData'] = $inside;
    echo json_encode($data);
});

$comp->slim->run();

if ($quit) {
    $comp->quit();
}
