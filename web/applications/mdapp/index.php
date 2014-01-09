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
# local functions for data-discovery module
require_once 'lib/search.php';

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


$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]"));
    $app->view()->appendData(array('pageName' => $GLOBALS['PAGE_NAME']));
});



$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/', function () use ($app) {
    $stash=index($app);
    $stash['m_dataset']['accepted']    = GetMetaData('accepted');
    $stash['m_dataset']['submitted']   = GetMetaData('submitted');
    $stash['srvr'] = "https://$_SERVER[HTTP_HOST]";
    return $app->render('html/main.html',$stash);
});


$app->get('/download-metadata/:udi', function ($udi) use ($app) {
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0]; 
    $met_file = "/sftp/data/$dataset[udi]/$dataset[udi].met";
    if (file_exists($met_file)) {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($info, $met_file);
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for i.e.
        header("Content-Type: $mime");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . filesize($met_file));
        header("Content-Disposition: attachment; filename=$dataset[metadata_filename]");
        readfile($met_file);
        exit;
    } else {
        drupal_set_message("Error retrieving metadata file: file not found: $met_file",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page (is there a better way to do this?
    }
});

$app->get('/download-metadata-db/:udi', function ($udi) use ($app) {
    # This SQL uses a subselect to resolve the newest registry_id
    # associated with the passed in UDI.
    $sql = "select metadata_xml from metadata 
            where registry_id = (   select registry_id 
                                    from curr_reg_view 
                                    where dataset_udi = ?
                                )";

    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $raw_data = $data->fetch(); 
    if ($raw_data) {
        $filename = "$udi-metadata.xml";
        # colons aren't allowed in filenames so substitute dash '-' character instead.
        $filename = preg_replace("/:/",'-',$filename); 
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for i.e.
        header("Content-Type: text/xml");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . strlen($raw_data['metadata_xml']));
        header("Content-Disposition: attachment; filename=$filename");
        ob_clean();
        flush();
        print $raw_data['metadata_xml'];
        exit;
    } else {
        drupal_set_message("Error retrieving metadata from database.",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page (is there a better way to do this?
    }
});

$app->post('/upload-new-metadata-file', function () use ($app) {
    $debug_st = print_r($_FILES,true);
    try {
        if (
            !isset($_FILES['newMetadataFile']['error']) ||
            is_array($_FILES['newMetadataFile']['error'])
        ) {
            throw new RuntimeException('Invalid parameters.');
        }

        switch ($_FILES['newMetadataFile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }

        if ($_FILES['newMetadataFile']['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }

        /*
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($_FILES['newMetadataFile']['tmp_name']['type']),
            array(
                'xml' => 'text/xml',
            ),
            true
        )) {
            throw new RuntimeException('Invalid file format detected. Please only attempt to upload XML metadata files.');
        }
        */


        $filename = $_FILES['newMetadataFile']['tmp_name'];
        $fhandle = fopen($filename,"r");
        $raw_xml = fread($fhandle,filesize($filename));
        fclose($fhandle);
        $xml = new SimpleXMLElement($raw_xml);
        $result = $xml->xpath('/gmd:geographicElement');
        if ($result === false) {
            throw new RuntimeException("Geolocation information required in xml.");
        } else {
            drupal_set_message("Geographic information detected in XML.",'status');
        }

        if (!move_uploaded_file(
            $_FILES['newMetadataFile']['tmp_name'],
            sprintf('./uploads/%s.%s',
                sha1_file($_FILES['newMetadataFile']['tmp_name']),
                $ext
            )
        )) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        drupal_set_message("File uploaded successfully.",'status');

    } catch (RuntimeException $e) {
        drupal_set_message("File upload error: ".$e->getMessage()."<br /><pre>$debug_st</pre>",'error');
        echo "<a href=.>Continue</a>";
    }
});

function index($app) {
    drupal_add_js("/$GLOBALS[PAGE_NAME]/includes/js/mdapp.js",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/mdapp.css",array('type'=>'external'));
    $stash['defaultFilter'] = $app->request()->get('filter');
    return $stash;
}

$app->run();


function GetMetadata($type) {
    $type=strtolower($type);
    switch($type) {
        case "accepted":
            /*
            $sql = "SELECT metadata_status, url_metadata, dataset_udi, dataset_metadata
                    FROM 
                    registry r2
                    INNER JOIN (
                        SELECT MAX(registry_id) AS MaxID
                        FROM registry
                        GROUP BY substr(registry_id,1,16)
                    ) m
                    ON r2.registry_id = m.MaxID
                    where metadata_status = 'Accepted' 
                    and url_metadata like '/sftp/data/%.met' 
                    order by registry_id";
            */
            # per Patrick, the curr_reg_view shows this same information, that being
            # the most current version of the metadata.
            $sql = "SELECT metadata_status, url_metadata, dataset_udi, dataset_metadata
                    FROM curr_reg_view 
                    where metadata_status = 'Accepted' 
                    and url_metadata like '/sftp/data/%.met' 
                    order by registry_id";
            break;
        case "submitted":
            $sql = "SELECT metadata_status, url_metadata, dataset_udi, dataset_metadata
                    FROM curr_reg_view 
                    where metadata_status = 'Submitted' 
                    and url_metadata like '/sftp/data/%.met' 
                    order by registry_id";
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


?>
