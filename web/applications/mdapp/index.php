<?php
# METADATA APPROVAL APPLICATION
# PPOC: Williamson

$GLOBALS['pelagos']['title'] = 'Metadata Approval Application';

# LOGFILE - SET THIS ACCORDINGLY
$GLOBALS['logfile_name']='mdapp.log';
date_default_timezone_set('America/Chicago');

# database utilities
require_once("/opt/pelagos/share/php/db-utils.lib.php");
# Framework (model/view)
require_once '/usr/local/share/Slim/Slim/Slim.php';
# templating engine - views
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';
# GRIIDC drupal extensions to allow use of drupal-intended code outside of drupal
require_once '/opt/pelagos/share/php/drupal.php';
# PHP streams anything in an includes/ directory.  This is for use WITH slim.
# if not using slim, use aliasIncludes.php instead.
require_once '/opt/pelagos/share/php/dumpIncludesFile.php';
# various functions for accessing the RIS database
require_once '/opt/pelagos/share/php/rpis.php';
# various functions for accessing GRIIDC datasets
require_once '/opt/pelagos/share/php/datasets.php';
# misc utilities and stuff...
require_once '/opt/pelagos/share/php/utils.php';
# LDAP functionality
require_once '/opt/pelagos/share/php/ldap.php';
# Twig templating engine autoloader
require_once 'Twig/Autoloader.php';

# add js library - informs drupal to add these standard js libraries upstream.
# can also use drupal_add_js to specify a full path to a js library to include.
# similarly, there is a drupal_add_css function.  These js includes are sent
# to the browser at the time drupal sends its own.  "system" is the main
# drupal module.
drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'jquery.cookie');
drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/OpenLayers.js',array('type'=>'external'));
drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false',array('type'=>'external'));
drupal_add_js('/includes/geoviz/geoviz.js','external');

global $user;
$GLOBALS['pelagos_config'] = parse_ini_file('/etc/opt/pelagos.ini',true);
$GLOBALS['module_config'] = parse_ini_file('config.ini',true);
$GLOBALS['logfile_location'] = $GLOBALS['pelagos_config']['paths']['log'].'/'.$GLOBALS['logfile_name'];
$GLOBALS['ldap'] = parse_ini_file($GLOBALS['pelagos_config']['paths']['conf'].'/ldap.ini',true);
$GLOBALS['smtp'] = parse_ini_file($GLOBALS['pelagos_config']['paths']['conf'].'/smtp.ini',true);
$GLOBALS['PAGE_NAME'] = preg_replace('/^\//', '', $_SERVER['SCRIPT_NAME']);

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
    $app->view()->appendData(array('pagelessBaseUrl' => "$protocol$env[SERVER_NAME]"));
    $app->view()->appendData(array('pageName' => $GLOBALS['PAGE_NAME']));
    $app->view()->appendData(array('jiraBase' => $GLOBALS['module_config']['jira']['jira_base']));
});

$app->hook('slim.before.router', function () use ($app) {
    global $user;
    if(isset($user->name)){
        $username = $user->name;
    } else {
        $username = '';
    }
    $applicationName="Metadata";
    $applicationRole="reviewers";
    if (memberHasApplicationRole($username,$applicationName,$applicationRole) == false) {
        drupal_set_message("Access Restricted",'error');
        print "The role of metadata reviewer is required for access.  Please contact GRIIDC for access.";
        $app->stop();
    }
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));
$app->get('/js/:name.js', function ($name) use ($app) {
    header('Content-type: text/javascript');
    $app->render("js/$name.js");
    exit;
});

$app->get('/css/:name.css', function ($name) use ($app) {
    header('Content-type: text/css');
    $app->render("css/$name.css");
    exit;
});

$app->get('/', function () use ($app) {
    return $app->render('html/main.html',index($app));
});

// Download from file on disk - probably going away, but not today..
$app->get('/download-metadata/:udi', function ($udi) use ($app) {
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(OpenDB('GOMRI_RO'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(OpenDB('GOMRI_RO'),array("udi=$udi"));
    }
    $dataset = $datasets[0];

    $dl_dir = $GLOBALS['pelagos_config']['paths']['data_download'];
    $met_file = "$dl_dir/$dataset[udi]/$dataset[udi].met";
    if (file_exists($met_file)) {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($info, $met_file);
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for i.e.
        header("Content-Type: $mime");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . filesize($met_file));
        header("Content-Disposition: attachment; filename='$dataset[metadata_filename]'");
        readfile($met_file);
        exit;
    } else {
        drupal_set_message("Error retrieving metadata file: file not found: '$met_file'",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page (is there a better way to do this?
    }
});

// change metadata_status field
$app->post('/change_status/:udi', function ($udi) use ($app) {
    global $user;
    $to = $app->request()->post('to');
    $from = getCurrentState($udi);
    /*
        BackToSubmitter
        Submitted
        InReview
        SecondCheck
        Accepted
    */

    $sql = "update registry set metadata_status = :to where
            metadata_status = :from and registry_id =
            ( select MAX(registry_id) from registry where dataset_udi = :udi)";

    $dbms = OpenDB("GOMRI_RW");
    $data = $dbms->prepare($sql);

    $data->bindParam(':to',$to);
    $data->bindParam(':from',$from);
    $data->bindParam(':udi',$udi);

    $data->execute();

    drupal_set_message("Metadata status for $udi has been changed from $from to $to.",'status');
    writeLog($user->name." has changed metadata status for $udi ($from -> $to)");
    drupal_goto($GLOBALS['PAGE_NAME']);
    drupal_exit();
});

// change approval flag status
$app->post('/change_appr_status/:udi', function ($udi) use ($app) {
    global $user;
    $approval = $app->request()->post('approval');

    $sql = "update registry set approval_status = :to
            where registry_id =
            ( select MAX(registry_id) from registry where dataset_udi = :udi)";

    if (in_array($approval,array('NotRequired','ApprovalRequired','Approved'))) {

        $dbms = OpenDB("GOMRI_RW");
        $data = $dbms->prepare($sql);

        $data->bindParam(':to',$approval);            # Bindparams are our friends.
        $data->bindParam(':udi',$udi);

        $data->execute();

        drupal_set_message("Metadata approval status for $udi has been changed to $approval.",'status');
        writeLog($user->name." has changed metadata approval status for $udi (to $approval)");
    }
    drupal_goto($GLOBALS['PAGE_NAME']);
    drupal_exit();
});

// Test Geometry
$app->post('/TestGeometry', function () use ($app) {
    // attempt to have PostGIS validate any geometry, if found.
    $coordinate_list = $app->request()->post('testPolygon');
    $xml = "<gml:Polygon gml:id=\"Polygon\" srsName=\"urn:ogc:def:crs:EPSG::4326\"><gml:exterior><gml:LinearRing><gml:coordinates>$coordinate_list</gml:coordinates></gml:LinearRing></gml:exterior></gml:Polygon>";
    $dbms = OpenDB("GOMRI_RO");
    $sql="select ST_GeomFromGML('$xml', 4326) as geometry";
    $data = $dbms->prepare($sql);
    if ($data->execute()) {
        drupal_set_message("Geometry verified by PostGIS as OK",'status');
        $tmp=$data->fetchAll();
        $geometry=$tmp[0]['geometry'];
     } else {
        $dbErr = $data->errorInfo();
        drupal_set_message("Geometry supplied is not valid (PostGIS said: $dbErr[2] <br />SQL: $sql)",'error');
      }
    $_SESSION['testPolygon'] = $_POST['testPolygon'];
    $options['fragment']="GeoTester";
    drupal_goto($GLOBALS['PAGE_NAME'].'/',$options,302); # reload calling page (is there a better way to do this?
});

// Download from XML in database
$app->get('/download-metadata-db/:udi', function ($udi) use ($app) {
    # This SQL uses a subselect to resolve the newest registry_id
    # associated with the passed in UDI.
    $sql = "
    select
        metadata_xml,
        concat(substring(metadata.registry_id from 1 for 16),'-metadata.xml') as filename
    FROM metadata left join registry on registry.registry_id = metadata.registry_id
    WHERE
        metadata.registry_id = (   select registry_id
                                    from registry_view
                                    where dataset_udi = ?
                                )";

    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $raw_data = $data->fetch();
    if ($raw_data) {
        // We changed from generating a filename to using the filename referenced in the XML.
        //$filename = "$udi-metadata.xml";
        $filename = preg_replace(array('/{/','/}/'),array('',''),$raw_data['filename']);
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
        drupal_set_message("Error retrieving metadata from database. ($sql)",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page (is there a better way to do this?
    }
});

$app->post('/upload-new-metadata-file', function () use ($app) {
    global $user;
    $geoflag='no';
    $arbitraryGML = 'no';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $env = $app->environment();
    $baseUrl = "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]";
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

        /*
        if ($_FILES['newMetadataFile']['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }
        */

        // read file into string
        $filename = $_FILES['newMetadataFile']['tmp_name'];
        $orig_filename = $_FILES['newMetadataFile']['name'];

        if(!file_exists($_FILES['newMetadataFile']['tmp_name'])) {
            throw new RuntimeException('Error: file uploaded no longer exists.');
        }

        if ($_FILES['newMetadataFile']['size'] == 0) {
            throw new RuntimeException('File uploaded was empty.  This may be caused by the use of the browser back button.');
        }

        // pattern match check file
        if(!preg_match('/-metadata.xml$/',$orig_filename)) {
            throw new RuntimeException("Bad filename $orig_filename. Filename must be in the form of UDI-metadata.xml.");
        }

        $udi = preg_replace('/-metadata.xml$/','',$orig_filename); # need to verify this!
        $udi = preg_replace('/-/',':',$udi);

        $fhandle = fopen($filename,"r");
        $raw_xml = fread($fhandle,filesize($filename));
        fclose($fhandle);

        // load XML into DOM
        libxml_use_internal_errors(true); // enables capture of error information
        $doc = new DomDocument('1.0','UTF-8');
        $tmpp = @$doc->loadXML($raw_xml);
        if (!$tmpp) {
            $err = libxml_get_last_error();
            $err_str = $err->message;
            throw new RuntimeException("Malformed XML: The XML file supplied $orig_filename could not be parsed. ($err_str)");
        }

        // Attempt to validate the XML file
        if(isset($_POST['validateSchema']) and $_POST['validateSchema'] == 'on') {
            $schemaErrors=0;
            $schema = 'http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd';
            if (!$doc->schemaValidate($schema)) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                for ($i=0; $i<sizeof($errors); $i++) {
                    switch ($errors[$i]->level) {
                        case LIBXML_ERR_WARNING:
                            $error = "WARNING (".$errors[$i]->code.") on XML line ".$errors[$i]->line.": ".$errors[$i]->message;
                            drupal_set_message($error,'warning');
                            break;
                        case LIBXML_ERR_ERROR:
                            $schemaErrors++;
                            $error = "ERROR (".$errors[$i]->code.") on XML line ".$errors[$i]->line.": ".$errors[$i]->message;
                            drupal_set_message($error,'error');
                            break;
                        case LIBXML_ERR_FATAL:
                            $schemaErrors++;
                            $error = "FATAL ERROR (".$errors[$i]->code.") on XML line ".$errors[$i]->line.": ".$errors[$i]->message;
                            drupal_set_message($error,'error');
                            break;
                    }
                }
            } else {
                drupal_set_message("XML Valid per ISO-19115-2 ",'status');
            }

            if($schemaErrors > 0) {
                throw new RuntimeException("The file uploaded as $orig_filename does not validate as ISO-19115-2 compliant XML.");
            }
        }



        // also load as simplxml object for quick xpath tests
        $xml = simplexml_import_dom($doc);

        // register namespaces just in case they are not already defined in the XML
        $xml->registerXPathNamespace('xmlns', 'http://www.isotc211.org/2005/gmi');
        $xml->registerXPathNamespace('gco',   'http://www.isotc211.org/2005/gco');
        $xml->registerXPathNamespace('gmd',   'http://www.isotc211.org/2005/gmd');
        $xml->registerXPathNamespace('gmi',   'http://www.isotc211.org/2005/gmi');
        $xml->registerXPathNamespace('gml',   'http://www.opengis.net/gml/3.2');
        $xml->registerXPathNamespace('gmx',   'http://www.isotc211.org/2005/gmx');
        $xml->registerXPathNamespace('gsr',   'http://www.isotc211.org/2005/gsr');
        $xml->registerXPathNamespace('gss',   'http://www.isotc211.org/2005/gss');
        $xml->registerXPathNamespace('gts',   'http://www.isotc211.org/2005/gts');
        $xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $xml->registerXPathNamespace('xsi',   'http://www.w3.org/2001/XMLSchema-instance');

        // Check for description field, save if found
        $extent_description=null;
        $check_desc_xpath = "/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:description[1]/gco:CharacterString[1]";
        $check_desc = $xml->xpath($check_desc_xpath);
        foreach ($check_desc as $node) { // only 1 possible
            $extent_description=$node;
        }

        // Check to see if filename matches existing UDI.
        if(!checkForUDI($udi)) {
            throw new RuntimeException("The UDI $udi is not found in the registry.");
        }

        $errors=array();
        // Check to see if filename matches XML internal filename reference
        $loc_1_xpath = "/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]"; # as filename
        $loc_1 = $xml->xpath($loc_1_xpath);
        if(isset($loc_1[0][0])) {
            $loc_1_val = $loc_1[0][0];
            $errmsg = textboxize("GRIIDC standards: Filename, $orig_filename does not match file identifier, $loc_1_val as UDI-metadata.xml (w/dash for colon)","/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]");
            if(!preg_match("/^$orig_filename$/",$loc_1_val)) {
                if (isset($_POST['test1']) and $_POST['test1']=='on') {
                    array_push($errors,$errmsg);
                } else {
                    drupal_set_message($errmsg,'warning');
                }
            }
        } else {
            $errmsg = textboxize("GRIIDC standards:  File identifier not set in file uploaded.","/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]");
            if (isset($_POST['test1']) and $_POST['test1']=='on') {
                array_push($errors,$errmsg);
            } else {
                drupal_set_message($errmsg,'warning');
            }
        }


        // Check to see if filename matches XML internal UDI reference #1
        $loc_2_xpath = "/gmi:MI_Metadata/gmd:dataSetURI[1]/gco:CharacterString[1]"; # as UDI
        $loc_2 = $xml->xpath($loc_2_xpath);
        if(isset($loc_2[0][0])) {
            $loc_2_val = $loc_2[0][0];
            $errmsg = textboxize("GRIIDC standards:  UDI, $udi does not match metadata URL, $loc_2_val","/gmi:MI_Metadata/gmd:dataSetURI/gco:CharacterString");
            if(!preg_match("/\/$udi$/",$loc_2_val)) { # URL must end with UDI
                if (isset($_POST['test2']) and $_POST['test2']=='on') {
                    array_push($errors,$errmsg);
                } else {
                    drupal_set_message($errmsg,'warning');
                }
            }
        } else {
            $errmsg = textboxize("GRIIDC standards:  Metadata URL not set in the file uploaded.","/gmi:MI_Metadata/gmd:dataSetURI/gco:CharacterString");
            if (isset($_POST['test2']) and $_POST['test2']=='on') {
                array_push($errors,$errmsg);
            } else {
                drupal_set_message($errmsg,'warning');
            }
        }

        // Check to see if filename matches XML internal UDI reference #2
        $loc_3_xpath = "/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1]";
        $loc_3 = $xml->xpath($loc_3_xpath);
        if(isset($loc_3[0][0])) {
            $loc_3_val = $loc_3[0][0];
            $errmsg = textboxize("GRIIDC standards:  UDI, $udi does not match distribution URL $loc_3_val.","/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1]");
            if(!preg_match("/\/$udi$/",$loc_3_val)) { # URL must end with UDI
                if (isset($_POST['test3']) and $_POST['test3']=='on') {
                    array_push($errors,$errmsg);
                } else {
                    drupal_set_message($errmsg,'warning');
                }
            }
        } else {
            $errmsg = textboxize("GRIIDC standards:  Distribution URL was not set in file uploaded.","/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1]");
            if (isset($_POST['test3']) and $_POST['test3']=='on') {
                array_push($errors,$errmsg);
            } else {
                drupal_set_message($errmsg,'warning');
            }
        }

        # If there were previous user-enabled hard errors, throw exception and display errors.
        if (count($errors) > 0) {
            $err_str='<ul>';
            foreach($errors as $error) {
                $err_str .= "<li>$error</li>\n";
            }
            $err_str.='</ul>';
            throw new RuntimeException($err_str);
        }

        $dbms = OpenDB("GOMRI_RW");
        try {
            $doc->normalizeDocument();
            $doc->formatOutput=true;
            $xml_save=$doc->saveXML();

            $dbms->beginTransaction();

            // query database for current (highest) registry_id for particular UDI
            $sql = "select max(registry_id) as newest_reg from registry where substring(registry_id, 1, 16) = ?";
            $data = $dbms->prepare($sql);
            $data->execute(array($udi));
            $tmp=$data->fetchAll(); $reg_id=$tmp[0]['newest_reg'];

            // query database to deteremine if metadata table is populated for this
            // registry ID, set bool variable.
            $sql = "SELECT COUNT(*) as cnt FROM metadata where registry_id = ?";
            $data = $dbms->prepare($sql);
            $data->execute(array($reg_id));
            $tmp=$data->fetchAll();
            $has_metadata_in_db=false;
            $has_metadata_in_db = ($tmp[0]['cnt'] ==  1);

            // override datestamp in XML, if option is selected
            if (isset($_POST['overrideDatestamp']) and $_POST['overrideDatestamp']=='on') {
                /* Potential Cases

                1) XML has no /gmi:MI_Metadata/gmd:dateStamp
                2) XML has existing /gmi:MI_Metadata/gmd:dateStamp/gco:Date
                3) XML has existing /gmi:MI_Metadata/gmd:dateStamp/gco:DateTime

                In all these cases, we the final XML to have /gmi:MI_Metadata/gmd:dateStamp/gco:DateTime
                of the current datetime.

                */

                $doc3 = new DomDocument('1.0','UTF-8');
                $tmpp = @$doc3->loadXML($xml_save);
                if (!$tmpp) {
                    $err = libxml_get_last_error();
                    $err_str = $err->message;
                    throw new RuntimeException("Malformed XML: The XML file supplied $orig_filename could not be parsed. ($err_str)");
                }

                $xpathdoc = new DOMXpath($doc3);
                // remove gco:Date if it has one
                $searchXpath = "/gmi:MI_Metadata/gmd:dateStamp/gco:Date";
                $elements = $xpathdoc->query($searchXpath);
                $node = $elements->item(0);
                if ($elements->length > 0) {
                    $parent = $node->parentNode;
                    $parent->removeChild($node);
                }

                // remove gco:DateTime if it has one
                $searchXpath = "/gmi:MI_Metadata/gmd:dateStamp/gco:DateTime";
                $elements = $xpathdoc->query($searchXpath);
                $node = $elements->item(0);
                if ($elements->length > 0) {
                    $parent = $node->parentNode;
                    $parent->removeChild($node);
                }

                // Add DateTime element
                $searchXpath = "/gmi:MI_Metadata/gmd:dateStamp";
                $elements = $xpathdoc->query($searchXpath);
                $node = $elements->item(0);
                addXMLChildValue($doc3,$node,'gco:DateTime',date("c"));
                $doc3->normalizeDocument();
                $doc3->formatOutput=true;
                $xml_save=$doc3->saveXML();
            }


            $geo_status='Nothing to verify';
            $geometery=null;
                // attempt to have PostGIS validate any geometry, if found and return the geometery
                $xml = simplexml_load_string($xml_save);
                $geo = $xml->xpath('/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_BoundingPolygon/gmd:polygon/*');
                if($geo) {  // double check for existance of geometry ( in case user override )
                    $geometry_xml = $geo[0]->asXML();
                    $sql2="select ST_GeomFromGML('$geometry_xml', 4326) as geometry, ST_AsText(ST_GeomFromGML('$geometry_xml', 4326)) as geometry_wkt";
                    $data2 = $dbms->prepare($sql2);
                    if ($data2->execute()) {
                        $geo_status = 'Verified by PostGIS as OK';
                        $tmp=$data2->fetchAll();
                        $geometry=$tmp[0]['geometry'];
                        $geometry_wkt=$tmp[0]['geometry_wkt'];
                        $geoflag = 'yes';
                        // Now determine an envelope that surrounds this geometry
                        $sql = "SELECT
                                    ST_AsText(ST_Envelope(:geo::geometry)) as \"geoenvelope\",
                                    ST_XMin(:geo::geometry) as \"westBoundLongitude\",
                                    ST_XMax(:geo::geometry) as \"eastBoundLongitude\",
                                    ST_YMin(:geo::geometry) as \"southBoundLatitude\",
                                    ST_YMax(:geo::geometry) as \"northBoundLatitude\"
                                    ";
                        $data3 = $dbms->prepare($sql);
                        $data3->bindParam(":geo",$geometry);
                        if ($data3->execute()) {
                            $tmp=$data3->fetchAll();
                            $envelope_wkt=$tmp[0]['geoenvelope'];
                            $westBoundLongitude=$tmp[0]['westBoundLongitude'];
                            $eastBoundLongitude=$tmp[0]['eastBoundLongitude'];
                            $southBoundLatitude=$tmp[0]['southBoundLatitude'];
                            $northBoundLatitude=$tmp[0]['northBoundLatitude'];
                            $envelope=<<<EOF
                                <gmd:geographicElement>
                                  <gmd:EX_GeographicBoundingBox>
                                    <gmd:westBoundLongitude>
                                      <gco:Decimal>$westBoundLongitude</gco:Decimal>
                                    </gmd:westBoundLongitude>
                                    <gmd:eastBoundLongitude>
                                      <gco:Decimal>$eastBoundLongitude</gco:Decimal>
                                    </gmd:eastBoundLongitude>
                                    <gmd:southBoundLatitude>
                                      <gco:Decimal>$southBoundLatitude</gco:Decimal>
                                    </gmd:southBoundLatitude>
                                    <gmd:northBoundLatitude>
                                      <gco:Decimal>$northBoundLatitude</gco:Decimal>
                                     </gmd:northBoundLatitude>
                                  </gmd:EX_GeographicBoundingBox>
                                </gmd:geographicElement>
EOF;

                            drupal_set_message("A bounding envelope has been calculated for this geometry.",'status');

                            // first load XML into DOM object for future manipulation
                            $doc = new DOMDocument('1.0','utf-8');
                            $doc->loadXML($xml_save);  // already checked for validity
                            $xpath = new DOMXpath($doc);

                            // locate any existing EX_GeographicBoundingBox and delete its parent.
                            $bbox_xpath = "/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox";
                            $bboxes = $xpath->query($bbox_xpath);
                            foreach ( $bboxes as $box ) {
                                $parent = $box->parentNode;
                                $grandparent = $parent->parentNode;
                                $grandparent->removeChild($parent);
                            }

                            // create new gmd:geographicElement for the bounding-box envelope
                            $fragment = $doc->createDocumentFragment();
                            // $fragment->appendChild($doc->createElement('gmd:geographicElement',$envelope));
                            $fragment->appendXML($envelope);

                            // locate parent node
                            $parent_xpath = '/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]';
                            $parent = $xpath->query('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]');
                            // locate reference node
                            $ref_xpath = '/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]';
                            $ref = $xpath->query($ref_xpath);

                            // insert into XML before first existing geographicElement
                            $parent->item(0)->insertBefore($fragment,$ref->item(0));
                            $doc->normalizeDocument();
                            $doc->formatOutput=true;
                            $xml_save=$doc->saveXML();

                        }
                    } else {
                        $dbErr = $data2->errorInfo();
                        $geo_status = "<font color=red>Rejected by PostGIS - ".$dbErr[2]."</font>";
                        throw new RuntimeException("PostGIS rejected the geometry found in $orig_filename.");
                    }
                }

            // insert (or update) data in metadata table

            // clean up formatting via tidy
            $tidy_config = array('indent' => true,'indent-spaces' => 4,'input-xml' => true,'output-xml' => true,'wrap' => 0);
            $tidy = new tidy;
            $tidy->parseString($xml_save, $tidy_config, 'utf8');
            $tidy->cleanRepair();

            $sql = '';
            if ($geoflag == 'yes') {
                if ($has_metadata_in_db) {
                    $sql = "update metadata set metadata_xml=?, geom=? where  registry_id = ?";
                } else {
                    $sql = "insert into metadata ( metadata_xml, geom, registry_id ) values (?,?,?)";
                }
                $data3 = $dbms->prepare($sql);
                if(!$data3->execute(array($tidy,$geometry,$reg_id,))) {
                    $err=$data3->errorInfo();
                    $err_str=$err[2];
                    throw new RuntimeException("Error saving to database: $err_str (p2=$geometry)");
                }
            } else {
                if ($has_metadata_in_db) {
                    $sql = "update metadata set metadata_xml=?, geom=null where  registry_id = ?";
                } else {
                    $sql = "insert into metadata ( metadata_xml, registry_id ) values (?,?)";
                }
                $data3 = $dbms->prepare($sql);
                if(!$data3->execute(array($tidy,$reg_id,))) {
                    $err=$data3->errorInfo();
                    $err_str=$err[2];
                    throw new RuntimeException("Error saving to database: $err_str");
                }
            }

            // check for description, if exists, insert into metadata table as well.
            if($extent_description) {
                $sql = "update metadata set extent_description = ? where registry_id = ?";
                // this is an update only because the preceeding block guarantees
                // that an entry exists in the metadata table.
                $data4 = $dbms->prepare($sql);
                if(!$data4->execute(array($extent_description,$reg_id,))) {
                    $err=$data4->errorInfo();
                    $err_str=$err[2];
                    throw new RuntimeException("Error saving extent description to database: $err_str");
                }
                drupal_set_message("An extent description found and stored.",'status');
            } else {
                $sql = "update metadata set extent_description = null where registry_id = ?";
                $data4 = $dbms->prepare($sql);
                if(!$data4->execute(array($reg_id,))) {
                    $err=$data4->errorInfo();
                    $err_str=$err[2];
                    throw new RuntimeException("Error saving extent description to database: $err_str");
                }
            }

            // update approved flag, if selected
            $flagged_accepted=false;
            $sql = '';
            if (isset($_POST['acceptMetadata']) and $_POST['acceptMetadata']=='on') {
                $flagged_accepted=true;
                $sql = "update registry set metadata_status = 'Accepted' where registry_id = ?";
            $data4 = $dbms->prepare($sql);
                if(!$data4->execute(array($reg_id,))) {
                    $err=$data4->errorInfo();
                    $err_str=$err[2];
                    throw new RuntimeException("Error saving to database: $err_str");
                }
            }

            drupal_set_message('Upload Successful','status');
            if(isset($envelope_wkt) and ($envelope_wkt != null)) {
                $thanks_msg = "
                            <div id=olmap style=\"width:600px; height:400px;\"></div>
                            <p>
                                <ul>
                                    <li> Registry ID: <a href=\"$protocol$env[SERVER_NAME]/data/$udi/\" target=0>$reg_id</a></li>
                                    <li> Uploaded filename: $orig_filename</li>
                                    <li> Geometry Detected: $geoflag</li>
                                    <li> Geometry Status: $geo_status </li>
                                </ul>
                            </p>";
            } else {
                $thanks_msg = "
                            <p>
                                <ul>
                                    <li> Registry ID: <a href=\"$protocol$env[SERVER_NAME]/data/$udi/\" target=0>$reg_id</a></li>
                                    <li> Uploaded filename: $orig_filename</li>
                                    <li> Geometry Detected: $geoflag</li>
                                    <li> Geometry Status: $geo_status </li>
                                </ul>
                            </p>";
            }

            drupal_set_message($thanks_msg,'status');
            $loginfo=$user->name." successfully uploaded metadata for $reg_id";
            if($flagged_accepted) {$loginfo .= " and data was flagged as accepted";}
            $loginfo .= '.'; // Punctuation is important.
            writeLog($loginfo);

            $dbms->commit();

        } catch (RuntimeException $ee){
            $dbms->rollBack();
            throw $ee;
        }
    } catch (RuntimeException $e) {
        $err_str=$e->getMessage();
        drupal_set_message($user->name.": File upload error: $err_str",'error');
        writeLog($user->name." ".$err_str);
    }
    if(isset($envelope_wkt) and ($envelope_wkt != null)) {
        echo "<script>var envelope_wkt = '$envelope_wkt'; var geometry_wkt = '$geometry_wkt';</script>";
        drupal_add_js("/$GLOBALS[PAGE_NAME]/js/mdapp-ul.js",array('type'=>'external'));
    }
    echo "<a href=.>Continue</a>";
});

// Get log entries (per UDI, or get them all
$app->get('/getlog(/)(:udi/?)', function ( $udi = '' ) {
    print "<ul>";
    $rawlog = file($GLOBALS['logfile_location']);
    if ($udi != '') {
        $entries = array_values(preg_grep("/$udi/",$rawlog));
    } else {
        $entries = $rawlog;
    }
    foreach ($entries as $entry) {
        print "<li>$entry</li>\n";
    }
    print "</ul>";
    drupal_exit();
});


function index($app) {
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/mdapp.js",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/css/mdapp.css",array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.5/js/jquery.dataTables.js',array('type'=>'external'));
    drupal_add_css('//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.5/css/jquery.dataTables.css',array('type'=>'external'));
    $stash['defaultFilter'] = $app->request()->get('filter');
    $stash['m_dataset']['accepted'] = GetMetadata('Accepted','as_array');
    $stash['m_dataset']['submitted'] = GetMetadata('Submitted','as_array');
    $stash['m_dataset']['inreview'] = GetMetadata('InReview','as_array');
    $stash['m_dataset']['secondcheck'] = GetMetadata('SecondCheck','as_array');
    $stash['m_dataset']['backtosubmitter'] = GetMetadata('BackToSubmitter','as_array');
    $stash['srvr'] = "https://$_SERVER[HTTP_HOST]";
    if(isset($_SESSION['testPolygon'])) { $stash['testPolygon'] = $_SESSION['testPolygon']; }
    return $stash;
}
/**
 * default route - nothing happens
 */
$app->get('/jiraLink', function () {
    // $GLOBALS['pelagos']['title'] = 'Jira Link Service';
    print 'This is the Jira Link service...';
    drupal_exit();
});
/**
 * update the Registry table with the supplied jira link value.
 * Use the $udi as a key to the Registry table.
 * How you ask ...
 * With the UDI parameter find the registry ID in the registry_view relation.
 * Use the found registry_id to find the row in the registry table.
 * Put the link value in the registry table.
 */
$app->put('/jiraLink/:udi/:linkValue', function ($udi,$jiraLinkValue) use ($app) {

    $env = $app->environment();
    $registryViewQuery = "SELECT dataset_udi, registry_id ".
            "FROM registry_view ".
            " WHERE dataset_udi = :udi";

    $updateRegistryQuery = "UPDATE registry SET jira_ticket = :jiraLinkValue WHERE registry_id = :registry_id";

    $dbms = OpenDB("GOMRI_RO");
    $statement = $dbms->prepare($registryViewQuery);

    $statement->bindParam(':udi',$udi);

    $statement->execute();
    $raw_data = $statement->fetchAll();

    if($raw_data == false || count($raw_data) == 0) {
        $status = new \Pelagos\HTTPStatus(500, 'No data from query.');
        http_response_code($status->getCode());
        print $status->asJSON();
    } else {
        $numRows = count($raw_data);
        $keys = array_keys($raw_data);
        $registryId = $raw_data[0]["registry_id"];

        $statement = $dbms->prepare($updateRegistryQuery);

        $statement->bindParam(':jiraLinkValue',$jiraLinkValue);
        $statement->bindParam(':registry_id',$registryId);
        $statement->execute();
        $msg = "Updated Jira link ".$jiraLinkValue." in Registry id: ".$registryId;
       // print '<br>'.$msg;
        $status = new \Pelagos\HTTPStatus(200,$msg);
        http_response_code($status->getCode());
    }
    drupal_exit();

    });

$app->run();

function addXMLChildValue($doc,$parent,$fieldname,$fieldvalue) {
    $fieldvalue = htmlspecialchars($fieldvalue, ENT_QUOTES | 'ENT_XML1', 'UTF-8');
    $child = $doc->createElement($fieldname);
    $child = $parent->appendChild($child);
    $value = $doc->createTextNode($fieldvalue);
    $value = $child->appendChild($value);
    return $child;
}

function createXmlNode($doc,$parent,$nodeName) {
    $node = $doc->createElement($nodeName);
    $node = $parent->appendChild($node);
    return $node;
}

function checkForUDI($udi) {
    $sql = "SELECT COUNT(*) FROM registry_view where dataset_udi = ?";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $result = $data->fetchAll();
    $count = $result[0]['count'];
    return ($count==1);
}

function getUserMail($gomri_userid) {
    $ldap = connectLDAP($GLOBALS['ldap']['ldap']['server']);
    $baseDN = 'dc=griidc,dc=org';
    $userDNs = getDNs($ldap,$baseDN,"uid=$gomri_userid");
    $cn = ''; $mail='';
    if (count($userDNs) > 0) {
        $userDN = $userDNs[0]['dn'];
        $attributes = getAttributes($ldap,$userDN,array('cn','mail'));
        if (count($attributes) > 0) {
            if (array_key_exists('cn',$attributes)) $cn = $attributes['cn'][0];
            if (array_key_exists('mail',$attributes)) $mail = $attributes['mail'][0];
            $ret['fullname']=$cn;
            $ret['email']=$mail;
            return $ret;
        }
    }
}

function sendEmail($to,$from,$sub,$message,$cc=null) {
   $smtp_server=$GLOBALS['smtp']['smtp']['server'];
   if($GLOBALS['module_config']['email']['disable_email'] == 1) {
        $cc_str = '';
        if($cc) {
            foreach ($cc as $cc_person) {
                $cc_str .= " $cc_person,";
            }
        }
        $cc_str=rtrim(trim($cc_str),",");
       drupal_set_message("The following email was disabled by ini setting. The following message was not sent.<br />To: $to<br />Cc: $cc_str<br />From: $from<br />Subject: $sub<br />SMTP Server: $smtp_server<br />Message: $message",'warning');
    } else {
        ini_set("SMTP",$smtp_server );
        $header = "From: <$from>\r\n";
        $header .= "Cc: ";
        foreach ($cc as $cc_line) {
            $header .= "$cc_line,";
        }
        $header .= "$from\r\n";
        mail($to,$sub,$message,$header);
    }
}

function GetMetadata($type,$format) {
    if(in_array($type,array('Accepted','Submitted','InReview','SecondCheck','BackToSubmitter'))) {
        $sql = "SELECT
                    registry_view.metadata_status, registry_view.url_metadata, registry_view.dataset_udi,
                    coalesce(
                      substring(metadata.registry_id from 1 for 16),
                      registry_view.dataset_metadata
                    ) as dataset_metadata,
                    (metadata_xml is not null) as hasxml,
                    registry_view.submittimestamp,
                    registry.approval_status as approval,
                    registry.jira_ticket
                FROM
                    registry_view left join metadata
                    ON registry_view.registry_id = metadata.registry_id
                    left join registry
                    ON registry_view.registry_id = registry.registry_id
                WHERE
                    registry_view.metadata_status = :status
                AND
                    registry_view.metadata_dl_status = 'Completed'";

        $dbms = OpenDB("GOMRI_RO");
        $data = $dbms->prepare($sql);
        $data->bindParam(":status",$type);
        $data->execute();
        if ($format == 'as_json') {
            return json_encode($data->fetchAll());

        } else {
            return $data->fetchAll();
        }
    } else {
        return;
    }
}

// Eventually this really needs to go into the database in
// some official capacity
function writeLog($message) {
    $logfile_location = $GLOBALS['logfile_location'];
    $dstamp = date('r'); // RFC 2822 standard
    file_put_contents($logfile_location,"$dstamp:$message\n", FILE_APPEND);
}

function getCurrentState($udi) {
    $sql  = "select metadata_status from registry_view where dataset_udi = :udi";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->bindParam(":udi",$udi);
    $data->execute();
    $raw_data = $data->fetch();
    $state = $raw_data['metadata_status'];
    return $state;
}

function getUDIPOC($udi) {
    $sql  = "select dataset_poc_email, dataset_poc_name from registry_view where dataset_udi = :udi";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->bindParam(":udi",$udi);
    $data->execute();
    $email = null; $name = null;
    if ($raw_data = $data->fetch() ) {
        $email = $raw_data['dataset_poc_email'];
        $name = $raw_data['dataset_poc_name'];
    }
    return "$name <$email>";
}

function getMetadataReviewers() {
    $users = getGroupMembers("cn=reviewers,ou=Metadata,ou=applications,dc=griidc,dc=org");
    $ldap = connectLDAP($GLOBALS['ldap']['ldap']['server']);
    $newUserArray=array();
    foreach ($users as $user_dn) {
       #ldap dn attributes
       $newUserArray[$user_dn]["mail"]  = null;
       $newUserArray[$user_dn]["cn"]    = null;
       $newUserArray[$user_dn]["title"] = null;
       $newUserArray[$user_dn]["uid"]   = null;
       $attributes = getAttributes($ldap,$user_dn,array("mail","cn","title","uid"));
       if(count($attributes > 0)) {
           foreach (array('mail','cn','title','uid') as $key) {
               if (array_key_exists($key,$attributes) and count($attributes[$key] > 0)) {
                   $newUserArray[$user_dn][$key] = $attributes[$key][0];
               }
           }
       }
    }
    ldap_unbind($ldap);
    return $newUserArray;
}

function textboxize($string,$xpath) {
    return "$string<textarea onclick=\"this.focus();this.select()\" readonly=\"readonly\" style=\"width: 100%\">$xpath</textarea>";
}

