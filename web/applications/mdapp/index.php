<?php
# METADATA APPROVAL APPLICATION
# Author: Williamson, DEC 2013

# database utilities
require_once("../quartz/php/db-utils.lib.php");
# Framework (model/view)
require_once '/usr/local/share/Slim/Slim/Slim.php';
# templating engine - views
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';
# GRIIDC drupal extensions to allow use of drupal-intended code outside of drupal
require_once '/usr/local/share/GRIIDC/php/drupal.php';
# PHP streams anything in an includes/ directory.  This is for use WITH slim.
# if not using slim, use aliasIncludes.php instead.
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';
# various functions for accessing the RIS database
require_once '/usr/local/share/GRIIDC/php/rpis.php';
# various functions for accessing GRIIDC datasets
require_once '/usr/local/share/GRIIDC/php/datasets.php';
# misc utilities and stuff...
require_once '/usr/local/share/GRIIDC/php/utils.php';

# add js library - informs drupal to add these standard js libraries upstream.  
# can also use drupal_add_js to specify a full path to a js library to include.
# similarly, there is a drupal_add_css function.  These js includes are sent
# to the browser at the time drupal sends its own.  "system" is the main
# drupal module. 
drupal_add_library('system', 'ui.tabs');

$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['TwigView']['twigDirectory'];

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/', function () use ($app) {
    $stash=index($app);
    $stash['m_dataset']['notaccepted'] = GetMetaData('notaccepted');
    $stash['m_dataset']['accepted']    = GetMetaData('accepted');
    $stash['m_dataset']['none']        = GetMetaData('none');
    $stash['m_dataset']['submitted']   = GetMetaData('submitted');
    $stash['srvr'] = "https://$_SERVER[HTTP_HOST]";
    return $app->render('html/datasets.html',$stash);
});

$app->run();

function GetMetadata($type) {
    $type=strtolower($type);
    switch($type) {
        case "notaccepted":
            $sql = "select metadata_status, url_metadata, dataset_udi from registry where ";
            #$sql = "select metadata_status, url_metadata, dataset_udi from curr_reg_view where ";
            $sql .= "(metadata_status <> 'Accepted' or metadata_status is null) ";
            $sql .= " and url_metadata like '/sftp/data/%.met' ";
            $sql .= "order by url_metadata asc";
            break;
        case "accepted":
            $sql = "select metadata_status, url_metadata, dataset_udi from registry where ";
            #$sql = "select metadata_status, url_metadata, dataset_udi from curr_reg_view where ";
            $sql .= "metadata_status = 'Accepted' and url_metadata like '/sftp/data/%.met' ";
            $sql .= "order by url_metadata asc";
            break;
        case "none":
            $sql = "select metadata_status, url_metadata, dataset_udi from registry where ";
            #$sql = "select metadata_status, url_metadata, dataset_udi from curr_reg_view where ";
            $sql .= "metadata_status = 'None' and url_metadata like '/sftp/data/%.met' ";
            $sql .= "order by url_metadata asc";
            break;
        case "submitted":
            $sql = "select metadata_status, url_metadata, dataset_udi from registry where ";
            #$sql = "select metadata_status, url_metadata, dataset_udi from curr_reg_view where ";
            $sql .= "metadata_status = 'Submitted' and url_metadata like '/sftp/data/%.met' ";
            $sql .= "order by url_metadata asc";
            break;
    }
    if(isset($sql)) {       
        $dbms = OpenDB("GOMRI_RO");
        $data = $dbms->prepare($sql);
        $data->execute();
        return $data->fetchAll();
    } else {
        return;
    }
}

function index($app) {
    drupal_add_js("/$GLOBALS[PAGE_NAME]/includes/js/mdapp.js",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/mdapp.css",array('type'=>'external'));
    $stash['defaultFilter'] = $app->request()->get('filter');
    return $stash;
}


?>
