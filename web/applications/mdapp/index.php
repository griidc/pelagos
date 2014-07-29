<?php
# METADATA APPROVAL APPLICATION
# Author: Michael Scott Williamson  DEC 2013, Updated May 2014

# Note: hardcoded smtp.tamucc.edu, triton.tamucc.edu (ldap) in file.

# LOGFILE - SET THIS ACCORDINGLY
$GLOBALS['logfile_name']='mdapp.log';
date_default_timezone_set('America/Chicago');

# database utilities
require_once("/usr/local/share/GRIIDC/php/db-utils.lib.php");
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
# LDAP functionality
require_once '/usr/local/share/GRIIDC/php/ldap.php';

# add js library - informs drupal to add these standard js libraries upstream.
# can also use drupal_add_js to specify a full path to a js library to include.
# similarly, there is a drupal_add_css function.  These js includes are sent
# to the browser at the time drupal sends its own.  "system" is the main
# drupal module.
drupal_add_library('system', 'ui.tabs');
drupal_add_library('system', 'jquery.cookie');

global $user;

$GLOBALS['config'] = parse_ini_file('config.ini',true);
$GLOBALS['storage'] = parse_ini_file('/etc/griidc/storage.ini',true);
$GLOBALS['logfile_location'] = $GLOBALS['config']['Logfiles']['logfilePath'].'/'.$GLOBALS['logfile_name'];

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
    $app->view()->appendData(array('pagelessBaseUrl' => "$protocol$env[SERVER_NAME]"));
    $app->view()->appendData(array('pageName' => $GLOBALS['PAGE_NAME']));
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
    #drupal_add_js('/includes/tablesorter/js/jquery.tablesorter.min.js',array('type'=>'external'));
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
   
    $dl_dir = $GLOBALS['storage']['storage']['data_download_store'];
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
                                    from curr_reg_view
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

        // pattern match check file
        if(!preg_match('/-metadata.xml$/',$orig_filename)) {
            throw new RuntimeException('Bad filename: Filename must be "UDI-metadata.xml"');
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
            throw new RuntimeException("Malformed XML: The XML file supplied could not be parsed. ($err_str)");
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
                drupal_set_message("XML validates as ISO-19115-2 compliant",'status');
            }
    
            if($schemaErrors > 0) {
                throw new RuntimeException("XML is not ISO-19115-2 compliant");
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
        $errmsg = "GRIIDC standards: Filename does not match file identifier. (/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]) as UDI-metadata.xml (w/dash for colon)";
        if(isset($loc_1[0][0])) {
            $loc_1_val = $loc_1[0][0];
            if(!preg_match("/^$orig_filename$/",$loc_1_val)) {
                if (isset($_POST['test1']) and $_POST['test1']=='on') {
                    array_push($errors,$errmsg);
                } else {
                    drupal_set_message($errmsg,'warning');
                }
            }
        } else {
            if (isset($_POST['test1']) and $_POST['test1']=='on') {
                array_push($errors,$errmsg);
            } else {
                drupal_set_message($errmsg,'warning');
            }
        }
        

        // Check to see if filename matches XML internal UDI reference #1
        $loc_2_xpath = "/gmi:MI_Metadata/gmd:dataSetURI[1]/gco:CharacterString[1]"; # as UDI
        $loc_2 = $xml->xpath($loc_2_xpath);
        $errmsg = "GRIIDC standards:  UDI does not match metadata URL (/gmi:MI_Metadata/gmd:dataSetURI/gco:CharacterString)";
        if(isset($loc_2[0][0])) {
            $loc_2_val = $loc_2[0][0];
            if(!preg_match("/\/$udi$/",$loc_2_val)) { # URL must end with UDI
                if (isset($_POST['test2']) and $_POST['test2']=='on') {
                    array_push($errors,$errmsg);
                } else {
                    drupal_set_message($errmsg,'warning');
                }
            }
        } else {
            if (isset($_POST['test2']) and $_POST['test2']=='on') {
                array_push($errors,$errmsg);
            } else {
                drupal_set_message($errmsg,'warning');
            }
        }

        // Check to see if filename matches XML internal UDI reference #2
        $loc_3_xpath = "/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1]";
        $loc_3 = $xml->xpath($loc_3_xpath);
        $errmsg = 'GRIIDC standards:  UDI does not match distribution URL (/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1])';
        if(isset($loc_3[0][0])) {
            $loc_3_val = $loc_3[0][0];
            if(!preg_match("/\/$udi$/",$loc_3_val)) { # URL must end with UDI
                if (isset($_POST['test3']) and $_POST['test3']=='on') {
                    array_push($errors,$errmsg);
                } else {
                    drupal_set_message($errmsg,'warning');
                }
            }
        } else {
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

        if(isset($_POST['allowAnyGML']) and ($_POST['allowAnyGML'] == 'on')) {
            $geoflag='yes';
            $arbitraryGML = 'yes';
        } else {
        // Determine geometry type
        if ($geo = $xml->xpath('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_BoundingPolygon[1]/gmd:polygon[1]/gml:Polygon[1]')) {
            // Polygon - Ideally this is case
            $geoflag='yes';
            $xpathdoc = new DOMXpath($doc);
            $searchXpath = "/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_BoundingPolygon[1]/gmd:polygon[1]/gml:Polygon[1]";
            $elements = $xpathdoc->query($searchXpath);
            // assuming the following attribute...  ;-)
            $node = $elements->item(0);
            $node->setAttribute('gml:id',"Polygon");
            $node->setAttribute('srsName',"urn:ogc:def:crs:EPSG::4326");
            $xml_save=$doc->saveXML();

        } elseif ($geo = $xml->xpath('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_GeographicBoundingBox')) {
            // If metadata has a bounding box, convert it to a polygon.
            $coords=array();
            $bounds=array('westBoundLongitude','eastBoundLongitude','southBoundLatitude','northBoundLatitude');
            foreach ($bounds as $boundry) {
                $coords[$boundry] = $xml->xpath("/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_GeographicBoundingBox/gmd:$boundry/gco:Decimal");
            }
            // enumerate polygons clockwise & repeat first point as last
            $coord_list  = $coords['northBoundLatitude'][0].','.$coords['westBoundLongitude'][0].' ';
            $coord_list .= $coords['northBoundLatitude'][0].','.$coords['eastBoundLongitude'][0].' ';
            $coord_list .= $coords['southBoundLatitude'][0].','.$coords['eastBoundLongitude'][0].' ';
            $coord_list .= $coords['southBoundLatitude'][0].','.$coords['westBoundLongitude'][0].' ';
            $coord_list .= $coords['northBoundLatitude'][0].','.$coords['westBoundLongitude'][0];

            $xpathdoc = new DOMXpath($doc);
            $searchXpath = "/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:extent/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox";
            $elements = $xpathdoc->query($searchXpath);
            $node = $elements->item(0);

            if ($elements->length > 0) {
                $parent = $node->parentNode;
                $parent->removeChild($node);

                $newnode = createXmlNode($doc,$parent,'gmd:EX_BoundingPolygon');
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gmd:polygon');
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gml:Polygon');
                $newnode->setAttribute('gml:id',"Polygon");
                $newnode->setAttribute('srsName',"urn:ogc:def:crs:EPSG::4326");
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gml:exterior');
                $parent = $newnode;
                $newnode = createXmlNode($doc,$parent,'gml:LinearRing');
                $parent = $newnode;

                addXMLChildValue($doc,$parent,'gml:coordinates',$coord_list);
                $geoflag='yes';
                $msg = "A bounding box was detected.  This has been converted into a polygon.";
                drupal_set_message($msg,'warning');
            }
        } else {
            $geoflag='no';
        }
        }

        $dbms = OpenDB("GOMRI_RW");
        try {
            $doc->normalizeDocument();
            $doc->formatOutput=true;
            $xml_save=$doc->saveXML();

            // substitute exterior for interior (assumed only for polygons)
            if ((preg_match('/gml:interior>/',$xml_save)) and ($arbitraryGML == 'no')) {
                $xml_save = preg_replace('/gml:interior>/','gml:exterior>',$xml_save);
                drupal_set_message('Exterior polygon boundries assumed','warning');
            }

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

            // override datestamp in XML
            if (isset($_POST['overrideDatestamp']) and $_POST['overrideDatestamp']=='on') {
                    $doc3 = new DomDocument('1.0','UTF-8');
                    $tmpp = @$doc3->loadXML($xml_save);
                    if (!$tmpp) {
                        $err = libxml_get_last_error();
                        $err_str = $err->message;
                        throw new RuntimeException("Malformed XML: The XML file supplied could not be parsed. ($err_str)");
                    }

                    $xpathdoc = new DOMXpath($doc3);
                    $searchXpath = "/gmi:MI_Metadata/gmd:dateStamp/gco:Date";
                    $elements = $xpathdoc->query($searchXpath);
                    $node = $elements->item(0);
                    if ($elements->length > 0) {
                        $parent = $node->parentNode;
                        $parent->removeChild($node);
                    }
                    addXMLChildValue($doc3,$parent,'gco:DateTime',date("c"));
                    $doc3->normalizeDocument();
                    $doc3->formatOutput=true;
                    $xml_save=$doc3->saveXML();
            }

            
            $geo_status='Nothing to verify';
            $geometery=null;
            if ($geoflag=='yes') {
                // attempt to have PostGIS validate any geometry, if found and return the geometery
                $xml = simplexml_load_string($xml_save);
                $geo = $xml->xpath('/gmi:MI_Metadata/gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:extent[1]/gmd:EX_Extent[1]/gmd:geographicElement[1]/gmd:EX_BoundingPolygon[1]/gmd:polygon[1]/*');
                if($geo) {  // double check for existance of geometry ( in case user override )
                    $geometry_xml = $geo[0]->asXML();
                    $sql2="select ST_GeomFromGML('$geometry_xml', 4326) as geometry";
                    $data2 = $dbms->prepare($sql2);
                    if ($data2->execute()) {
                        $geo_status = 'Verified by PostGIS as OK';
                        $tmp=$data2->fetchAll();
                        $geometry=$tmp[0]['geometry'];
                    } else {
                        $dbErr = $data2->errorInfo();
                        $geo_status = "<font color=red>Rejected by PostGIS - ".$dbErr[2]."</font>";
                        throw new RuntimeException("PostGIS rejected geometry supplied");
                    }
                } else {
                    $geoflag = 'no';
                    drupal_set_message("A geometry was not found yet the 'Accept any valid GML' option was selected.",'warning');
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

            // send email if accepted and mail flag is set
            $dm_contacted=false;
            $dataManager=getDataManagerOldDataModel($udi); #array  ('fullname', 'email')
            $to_address_string = $dataManager['fullname'].'<'.$dataManager['email'].'>';
            $userMail=getUserMail($user->name); #array  ('fullname', 'email')
            if (isset($_POST['acceptMetadata']) and $_POST['acceptMetadata']=='on' and isset($_POST['contactOwner']) and $_POST['contactOwner']=='on') {
                $dm_contacted=true;
                sendEmail($to_address_string,$userMail['email'],"$udi metadata","The metadata for $udi has been accepted by GRIIDC.  Thank you!");
                drupal_set_message("An email of this approval has been sent to ".$dataManager['fullname'].'('.$dataManager['email'].')','status');
            }

            $thanks_msg = "Thank you ".$user->name.".  The metadata file for registry ID $reg_id has been recorded into the database.
                            <p>
                            Details:
                                <ul>
                                    <li> Registry ID: <a href=\"$protocol$env[SERVER_NAME]/data/$udi/\" target=0>$reg_id</a></li>
                                    <li> UDI: $udi</a></li>
                                    <li> Uploaded filename: $orig_filename</li>
                                    <li> Geometry Detected: $geoflag</li>
                                    <li> Geometry Status: $geo_status </li>
                                </ul>
                            </p>";

            drupal_set_message($thanks_msg,'status');
            $loginfo=$user->name." successfully uploaded metadata for $reg_id";
            if($flagged_accepted) {$loginfo .= " and data was flagged as accepted";}
            if($dm_contacted) { $loginfo .= " and data manager was emailed"; }
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
    drupal_add_js('https://cdn.datatables.net/1.10.0/js/jquery.dataTables.js',array('type'=>'external'));
    drupal_add_css('https://cdn.datatables.net/1.10.0/css/jquery.dataTables.css',array('type'=>'external'));
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
    $sql = "SELECT COUNT(*) FROM curr_reg_view where dataset_udi = ?";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $result = $data->fetchAll();
    $count = $result[0]['count'];
    return ($count==1);
}

function getDataManager($udi) {
    // returns: array  ('fullname', 'email')
    $sql = 'SELECT
    "EmailInfo_Address", coalesce("Person_HonorificTitle",\'\')||
    \' \'||"Person_FirstName"||\' \'||coalesce("Person_MiddleName",\'\')||
    \' \'||"Person_LastName"||\' \'||coalesce("Person_NameSuffix",\'\') as fullname
    FROM
    "HRI"."Dept-GoMRIPerson-Project-Role",
    "HRI"."EmailInfo",
    "HRI"."Person",
    "HRI"."Project"
    WHERE
    "EmailInfo"."Person_Number" = "Person"."Person_Number" AND
    "Person"."Person_Number" = "Dept-GoMRIPerson-Project-Role"."Person_Number"
    AND "Dept-GoMRIPerson-Project-Role"."ProjRole_Number" = 3
    AND "Dept-GoMRIPerson-Project-Role"."Project_Number" = "Project"."Project_Number"
    AND "Project"."FundingEnvelope_Cycle" = ? and "Project"."Project_Number" = ?';

    $dbms = OpenDB("GRIIDC_RO");
    $data = $dbms->prepare($sql);

    $fundingCycle=substr($udi,0,1).'0'.substr($udi,1,1);
    $fundingCycle=preg_replace('/Y01/','B01',$fundingCycle);
    $projSec=substr($udi,4,3);
    $data->execute(array($fundingCycle,$projSec));
    $result = $data->fetchAll();
    // will only have one
    $email = $result[0]['EmailInfo_Address'];
    $fullname = $result[0]['fullname'];
    $ret['fullname']=$fullname;
    $ret['email']=$email;
    return $ret;
}

function getDataManagerOldDataModel($udi) {
    // returns: array  ('fullname', 'email')
    $sql = "
    SELECT
        People.People_Email as EmailInfo_Address,
        concat(
            coalesce(People.People_Title,''),
            ' ',
            People.People_FirstName,
            ' ',
            coalesce(People.People_MiddleName,''),
            ' ',
            People.People_LastName,
            ' ',
            coalesce(People.People_Suffix)
        ) as fullname
    FROM
        People
            JOIN ProjPeople
                ON People.People_ID = ProjPeople.People_ID
            JOIN Programs
                ON ProjPeople.Program_ID = Programs.Program_ID
            JOIN FundingSource
                ON Programs.Program_FundSrc = FundingSource.Fund_ID
            JOIN Roles
                ON ProjPeople.Role_ID = Roles.Role_ID
    WHERE FundingSource.Fund_Name like ?
    AND Programs.Program_ID = ?
    AND Role_Name = 'Project Data Point of Contact'
";

    $dbms = OpenDB("RIS_RO");
    $data = $dbms->prepare($sql);

    $projSec=substr($udi,4,3);

    $fundingCycle=substr($udi,0,2);
    switch ($fundingCycle) {
        case "Y1":
            $fc='Year One Block Grant';
            break;
        case "R1":
            $fc='RFP-I';
            break;
        case "R2":
            $fc='RFP-II';
            break;
        case "R3":
            $fc='RFP-III';
            break;
    }

    $data->execute(array("%$fc%",$projSec));
    if ($result = $data->fetchAll()) {
        // will only have one
        $email = $result[0]['EmailInfo_Address'];
        $fullname = $result[0]['fullname'];
        $ret['fullname']=$fullname;
        $ret['email']=$email;
        return $ret;
    } else {
        return;
    }
}

function getUserMail($gomri_userid) {
    $ldap = connectLDAP('triton.tamucc.edu');
    $baseDN = 'dc=griidc,dc=org';
    $userDNs = getDNs($ldap,$baseDN,"uid=$gomri_userid");
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
   if($GLOBALS['config']['email']['disable_email'] == 1) {
        $cc_str = '';
        foreach ($cc as $cc_person) {
            $cc_str .= "$cc_person,";
        }
       drupal_set_message("The following email was disabled by ini setting. The following message was not sent.<br />To: $to<br />CC: $cc_str<br />From: $from<br />Subject: $sub<br />Message: $message",'warning'); 
    } else { 
        ini_set("SMTP","smtp.tamucc.edu" );
        $header = "From: <$from>\r\n";
        $header .= "CC: ";
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
                    curr_reg_view.metadata_status, curr_reg_view.url_metadata, curr_reg_view.dataset_udi,
                    coalesce(
                      substring(metadata.registry_id from 1 for 16),
                      curr_reg_view.dataset_metadata
                    ) as dataset_metadata,
                    (metadata_xml is not null) as hasxml,
                    curr_reg_view.submittimestamp,
                    registry.approval_status as approval
                FROM
                    curr_reg_view left join metadata
                    ON curr_reg_view.registry_id = metadata.registry_id
                    left join registry
                    ON curr_reg_view.registry_id = registry.registry_id
                WHERE
                    curr_reg_view.metadata_status = :status
                AND
                    curr_reg_view.metadata_dl_status = 'Completed'";
        
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
    $sql  = "select metadata_status from curr_reg_view where dataset_udi = :udi";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->bindParam(":udi",$udi);
    $data->execute();
    $raw_data = $data->fetch();
    $state = $raw_data['metadata_status'];
    return $state;
}

function getUDIPOC($udi) {
    $sql  = "select dataset_poc_email, dataset_poc_name from curr_reg_view where dataset_udi = :udi";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->bindParam(":udi",$udi);
    $data->execute();
    $raw_data = $data->fetch();
    $email = $raw_data['dataset_poc_email'];
    $name = $raw_data['dataset_poc_name'];
    return "$name <$email>";
}
?>
