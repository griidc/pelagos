<?php
# PHP Sharedir
$GRIIDC_PHP="/usr/local/share/GRIIDC/php"; # normally /usr/local/share/GRIIDC/php

# Framework (model/view)
require_once '/usr/local/share/Slim/Slim/Slim.php';
# templating engine - views
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';
# GRIIDC drupal extensions to allow use of drupal-intended code outside of drupal
require_once "$GRIIDC_PHP/drupal.php";
# PHP streams anything in an includes/ directory.  This is for use WITH slim.
# if not using slim, use aliasIncludes.php instead.
require_once "$GRIIDC_PHP/dumpIncludesFile.php";
# various functions for accessing the RIS database
require_once "$GRIIDC_PHP/rpis.php";
# various functions for accessing GRIIDC datasets
require_once "$GRIIDC_PHP/datasets.php";
# misc utilities and stuff...
require_once "$GRIIDC_PHP/utils.php";
# local functions for data-discovery module
require_once 'lib/search.php';
# local functions for the packaging sub-module to the data-discovery module
require_once 'lib/package.php';
# OpenID API for PHP
require_once '/usr/local/share/lightopenid-lightopenid/openid.php';
# GRIIDC database utilities
require_once "$GRIIDC_PHP/db-utils.lib.php";
# Auth library
require_once "$GRIIDC_PHP/auth.php";
# LDAP
require_once "$GRIIDC_PHP/ldap.php";

date_default_timezone_set('UTC');

# add js library - informs drupal to add these standard js libraries upstream.
# can also use drupal_add_js to specify a full path to a js library to include.
# similarly, there is a drupal_add_css function.  These js includes are sent
# to the browser at the time drupal sends its own.  "system" is the main
# drupal module.
drupal_add_library('system', 'ui.tabs');

$GLOBALS['griidc'] = parse_ini_file('/etc/opt/pelagos.ini',true);
$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['TwigView']['twigDirectory'];

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->hook('slim.before', function () use ($app) {
    global $user;
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]"));
    $app->view()->appendData(array('hostname' => $env['SERVER_NAME']));
    $app->view()->appendData(array('pageName' => $GLOBALS['PAGE_NAME']));
    $app->view()->appendData(array('currentPage' => urlencode(preg_replace('/^\//','',$_SERVER['REQUEST_URI']))));
    if (!empty($user->name)) {
        $app->view()->appendData(array('uid' => $user->name));
    }
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    header('Content-type: text/javascript');
    $stash['logged_in'] = (user_is_logged_in_somehow());
    $app->render("js/$name.js",$stash);
    exit;
});

$app->get('/css/:name.css', function ($name) use ($app) {
    header('Content-type: text/css');
    $app->render("css/$name.css");
    exit;
});

$app->get('/', function () use ($app) {
    drupal_add_js('/includes/openlayers/lib/OpenLayers.js',array('type'=>'external'));
    drupal_add_js('//maps.google.com/maps/api/js?v=3&sensor=false',array('type'=>'external'));
    drupal_add_js('/includes/geoviz/geoviz.js','external');
    $stash=index($app);
    # for now, only do this for guestAuthUser people, GoMRI auto-download is handled elsewhere.
    if( (isset($_COOKIE['dl_attempt_udi_cookie'])) and user_is_logged_in_somehow() ) {
        $udi =  $_COOKIE['dl_attempt_udi_cookie'];
        unset($_COOKIE['dl_attempt_udi_cookie']);
        # remove cookie
        setcookie('dl_attempt_udi_cookie', "", time() - 3600, '/', $_SERVER['SERVER_NAME']);
        $env = $app->environment();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $stash['download']=$udi;
        if (isset($_SESSION['guestAuthUser'])) drupal_set_message("Guest access enabled for ".$_SESSION['guestAuthUser'],'status');
    }
    return $app->render('html/index.html',$stash);
});

$app->post('/', function () use ($app) {
    $stash = index($app);
    # regardless of user-friendly javascript warning elsewhere, this will disallow 
    # unauthorized downloads server-side with a silent fail and reflow of the page.
    if (user_is_logged_in_somehow()) {
        $stash['download'] = $app->request()->post('download');
        $stash['srvr'] = "https://$_SERVER[HTTP_HOST]";
    }
    return $app->render('html/index.html',$stash);
});

function index($app) {
    drupal_add_js('/includes/tinyscrollbar/jquery.tinyscrollbar.min.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.events.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.min.js',array('type'=>'external'));
    drupal_add_js('/tree/js/tree.js',array('type'=>'external'));
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/search.js",array('type'=>'external'));
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/package.js",array('type'=>'external'));
    drupal_add_library('system', 'jquery.cookie');
    drupal_add_css("/$GLOBALS[PAGE_NAME]/css/search.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/scrollbars.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/datasets.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/dataset_details.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/dataset_download.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/logins.css",array('type'=>'external'));
    if (array_key_exists('treePaneCollapsed',$GLOBALS['config']['DataDiscovery'])) {
        $stash['treePaneCollapsed'] = $GLOBALS['config']['DataDiscovery']['treePaneCollapsed'];
    }
    else {
        $stash['treePaneCollapsed'] = 0;
    }
    $stash['defaultFilter'] = $app->request()->get('filter');
    return $stash;
}

$app->get('/datasets/:filter/:by/:id/:geo_filter', function ($filter,$by,$id,$geo_filter) use ($app) {
    $approvedMetadata = getApprovedMetadataUDIs();
    $stash = array();
    $stash['registered_datasets'] = array();
    $stash['identified_datasets'] = array();

    $reg_filters = array('registry_id!=00%');

    if (!empty($by)) {
        if ($by == 'otherSources') {
            $reg_filters[] = 'registry_id=00%';
        }
        elseif ($by == 'otherSource') {
            $reg_filters[] = "registry_id=00.x$id%";
        }
        else {
            if ($by != 'projectId') {
                if ($by == 'YR1') {
                    $filters = array("fundSrc>0","fundSrc<6");
                }
                else {
                    $filters = array("$by=$id");
                }
                $projects = getProjectDetails(getDBH('RPIS'),$filters);
                $projectIds = array();
                foreach ($projects as $project) {
                    $projectIds[] = $project['ID'];
                }
                $by = 'projectIds';
                $id = implode(',', $projectIds);
            }
            $reg_filters[] = "$by=$id";
            $reg_filters[] = 'registry_id!=00%';
        }
    }

    if (!empty($geo_filter) and $geo_filter != 'undefined') {
        $reg_filters[] = "geo_filter=$geo_filter";
    }

    # toggle whether to enforce new availability rules per ini file parameter
    if( (isset($GLOBALS['griidc']['syswide']['enforce_approved_metadata'] ) and ( $GLOBALS['griidc']['syswide']['enforce_approved_metadata'] == 1 ))) {
        $unrestricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('availability=available')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
        $restricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('availability=available_with_restrictions')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
        $md_under_review_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('availability=unavailable_pending_metadata_acceptance')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
        $unavailable_datasets = get_identified_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('availability=unavailable_pending_metadata_submission,unavailable_pending_data_submission,unavailable_pending_registration')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
    } else {
        $unrestricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('availability=available,unavailable_pending_metadata_submission,unavailable_pending_metadata_acceptance','restricted=0')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
        $restricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('availability=available_with_restrictions,unavailable_pending_metadata_submission,unavailable_pending_metadata_acceptance','restricted=1')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
        $md_under_review_datasets = array();
        $unavailable_datasets = get_identified_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('availability=unavailable_pending_data_submission,unavailable_pending_registration')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
    }

    foreach ($unrestricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset);
        $stash['unrestricted_datasets'][] = $dataset;
    }

    foreach ($restricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset);
        $stash['restricted_datasets'][] = $dataset;
    }
        
    foreach ($md_under_review_datasets as $dataset) {
        add_project_info($dataset);
        add_download_size($dataset);
        $stash['md_under_review_datasets'][] = $dataset;
    }

    foreach ($unavailable_datasets as $dataset) {
        add_project_info($dataset);
        $stash['identified_datasets'][] = $dataset;
    }

    $stash['filt'] = $filter;
    $app->render('html/datasets.html',$stash);
    exit;
})->conditions(array('filter' => '.*', 'by' => '.*', 'id' => '.*', 'geo_filter' => '.*'));

$app->get('/dataset_details/:udi', function ($udi) use ($app) {
    # used by a javascript that displays details on each
    # of the datasets in an expandable/collapsable manner
    if (preg_match('/^00/',$udi)) {
        $stash['datasets'] = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $stash['datasets'] = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }

    $app->render('html/dataset_details.html',$stash);
    exit;  # prevents Drupal wrapper in output
});


$app->get('/metadata/:udi', function ($udi) use ($app) {
    if (isMetadataApproved($udi)) {
    // if there is a file on disk, capture it
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];
    
    $disk_metadata_file_mimetype = '';
    $disk_metadata_file = '';
    $met_file = "/$GLOBALS[griidc][paths][download]/$dataset[udi]/$dataset[udi].met";
    if (file_exists($met_file)) {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $disk_metadata_file_mimetype = finfo_file($info, $met_file);
        $disk_metadata_file=file_get_contents($met_file);
    }
    # This SQL uses a subselect to resolve the newest registry_id
    # associated with the passed in UDI.
    $sql = "
    select 
        metadata_xml, 
        coalesce(
            cast(
                xpath('/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]/text()',metadata_xml,
                    ARRAY[
                    ARRAY['gmi', 'http://www.isotc211.org/2005/gmi'],
                    ARRAY['gmd', 'http://www.isotc211.org/2005/gmd'],
                    ARRAY['gco', 'http://www.isotc211.org/2005/gco']
                    ]
                ) as character varying
            ), 
            dataset_metadata
        ) 

    as filename  
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
        # Serve it out from the data in the database by default
        # the following line is probably better done in SQL, so this will be changed in the near future
        $filename = preg_replace(array('/{/','/}/'),array('',''),$raw_data['filename']);
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
    } elseif(strlen($disk_metadata_file) > 0) {
        # Serve it out from the data in the filesystem if it wasn't in the database
        $filename=$dataset['metadata_filename'];
        
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for i.e.
        header("Content-Type: $disk_metadata_file_mimetype");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . filesize($met_file));
        header("Content-Disposition: attachment; filename=$dataset[metadata_filename]");
        ob_clean();
        flush();
        readfile($met_file);
        exit;
    } else {
        drupal_set_message("Error retrieving metadata from database and filesystem.",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page
    }
    } else {
        drupal_set_message("Metadata that has not been approved cannot be downloaded.",'error');
        drupal_goto($GLOBALS['PAGE_NAME']); # reload calling page
    }
})->conditions(array('udi' => '(00|Y1|R\d)\.x\d{3}\.\d{3}:\d{4}'));

$app->get('/metadata/', function () use ($app) {
    $files = array();
    $directories = array('GoMRI','Others');
    foreach ($directories as $directory) {
        $dir_files = get_accepted_metadata($directory);
        $max_last_modified_ts = strtotime('2010-04-20');
        foreach ($dir_files as $file) {
            if ($file['last_modified_datetime'] != '{}') {
                $last_modified_ts = strtotime(preg_replace(array('/{/','/}/'),array('',''),$file['last_modified_datetime']));
            }
            else {
                $last_modified_ts = strtotime(preg_replace(array('/{/','/}/'),array('',''),$file['last_modified_date']));
            }
            if ($last_modified_ts > $max_last_modified_ts) $max_last_modified_ts = $last_modified_ts;
        }
        array_push($files,array('name' => "$directory/", 'type' => 'folder', 'last_modified' => strftime('%d-%b-%Y %H:%M',$max_last_modified_ts)));
    }
    $stash = array('directory' => 'metadata',
                   'parent' => '',
                   'filename_max_len' => 24,
                   'files' => $files
             );
    $app->render('html/waf.html',$stash);
    drupal_exit();
});

$app->get('/metadata/:directory/', function ($directory) use ($app) {
    $files = get_accepted_metadata($directory);
    $filename_max_len = 0;
    for ($i=0; $i<count($files); $i++) {
        $files[$i]['name'] = preg_replace('/:/','-',$files[$i]['dataset_udi']) . '-metadata.xml';
        $files[$i]['type'] = 'text';
        $size = strlen($files[$i]['metadata_xml']);
        if ($size > 1024000) {
            $size = round($size / 1024 / 1024);
            $size .= 'M';
        }
        elseif ($size > 999) {
            $size = round($size / 1024);
            $size .= 'K';
        }
        else {
            $size .= ' ';
        }
        $files[$i]['size'] = sprintf('% 4s',$size);
        if ($files[$i]['last_modified_datetime'] != '{}') {
            $last_modified_ts = strtotime(preg_replace(array('/{/','/}/'),array('',''),$files[$i]['last_modified_datetime']));
        }
        else {
            $last_modified_ts = strtotime(preg_replace(array('/{/','/}/'),array('',''),$files[$i]['last_modified_date']));
        }
        $files[$i]['last_modified'] = strftime('%d-%b-%Y %H:%M',$last_modified_ts);
        if (strlen($files[$i]['name']) > $filename_max_len) $filename_max_len = strlen($files[$i]['name']) + 1;
    }
    if ($filename_max_len < 24) $filename_max_len = 24;
    $stash = array('directory' => "metadata/$directory",
                   'parent' => '/metadata',
                   'filename_max_len' => $filename_max_len,
                   'files' => $files
             );
    $app->render('html/waf.html',$stash);
    drupal_exit();
})->conditions(array('directory' => 'GoMRI|Others'));

$app->get('/metadata/:directory/:file', function ($directory,$file) use ($app) {
    $udi = preg_replace('/-metadata.xml$/','',$file);
    $udi = preg_replace('/-/',':',$udi);
    $dbms = OpenDB("GOMRI_RO");
    $SQL = "SELECT dataset_udi,metadata_xml FROM curr_reg_view JOIN metadata on metadata.registry_id = curr_reg_view.registry_id WHERE metadata_status = 'Accepted' AND dataset_udi = ?";
    $data = $dbms->prepare($SQL);
    $data->execute(array($udi));
    $raw_data = $data->fetch();
    if ($raw_data) {
        header("Cache-Control: public"); // needed for i.e.
        header("Content-Type: text/xml");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . strlen($raw_data['metadata_xml']));
        header("Content-Disposition: inline; filename=$file");
        print $raw_data['metadata_xml'];
        drupal_exit();
    }
});

$app->get('/download-external/:udi', function ($udi) use ($app) {
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];
    $stash['dataset'] = $dataset;
    $app->render('html/download-external.html',$stash);
    exit;
});

$app->get('/download/:udi', function ($udi) use ($app) {
    # This is used in an ajax call to populate a div
    # that displays when an authenticated user clicks
    # the box icon on the right-hand pane of the data
    # discovery module.
    global $user;
    if (!user_is_logged_in_somehow()) {
        #$stash['error_message'] = "You must be logged in to download datasets.";
        #$app->render('html/download_error.html',$stash);
        drupal_exit();
    }
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    } else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];
    list($fs_hash_md5,$fs_hash_sha1,$fs_hash_sha256) = preg_split("/\|/",getHashes($udi));

    if ($dataset['access_status'] == "Restricted") {
        $stash['error_message'] = "This dataset is restricted for author use only.";
        $app->render('html/download_error.html',$stash);
        exit;
    }

    if ($dataset['access_status'] == "Approval") {
        $stash['error_message'] = "This dataset can only be downloaded with author approval.";
        $app->render('html/download_error.html',$stash);
        exit;
    }

    $approved_md_udis=getApprovedMetadataUDIs(); 
    $dl_ok = 0;
    if( (isset($approved_md_udis["$udi"])) or (!(( isset($GLOBALS['griidc']['syswide']['enforce_approved_metadata'] ) and ( $GLOBALS['griidc']['syswide']['enforce_approved_metadata'] == 1 ))))) {
        $dl_ok = 1;
    } else {
        $dl_ok = 0;
    }
    if ($dl_ok == 0) {
        $stash['error_message'] = "This dataset can not be downloaded because its metadata has not been approved.";
        $app->render('html/download_error.html',$stash);
        exit;
    }

    $dat_file = $GLOBALS['griidc']['paths']['data_download']."/$dataset[udi]/$dataset[udi].dat";
    
    $env = $app->environment();
    $stash = array();
    $stash['server'] = $env['SERVER_NAME'];
    $stash['dataset'] = $dataset;
    $stash['bytes']=0;
    if(file_exists($dat_file)) {
        $stash['bytes'] = filesize($dat_file);
    }
    $stash['filesize'] = bytes2filesize($stash['bytes'],1);
    $stash['filt'] = $app->request()->get('filter');
    $stash['fs_hash_md5'] = $fs_hash_md5;
    $stash['fs_hash_sha1'] = $fs_hash_sha1;
    $stash['fs_hash_sha256'] = $fs_hash_sha256;

    if ($GLOBALS['config']['DataDiscovery']['alternateDownloadSite'] == 1) {
        $host = $GLOBALS['config']['DataDiscovery']['alternateDownloadSiteServer'];
        $return = exec("ssh apache@$host -C ls $dat_file");
        if (preg_match("/$dataset[udi].dat/",$return)) {
            if((isset($_SESSION['guestAuthUser']) and ($_SESSION['guestAuthUser'] == true))) {
                $stash['guest']=1;
            } else {
                $stash['guest']=0;
            }
            $app->render('html/download.html',$stash);
            exit;
        } else {
            $stash['error_message'] = "Error retrieving data file: file not found (on $host): $dat_file";
            $app->render('html/download_error.html',$stash);
        exit;
        }
    } elseif (file_exists($dat_file)) {
        
        if((isset($_SESSION['guestAuthUser']) and ($_SESSION['guestAuthUser'] == true))) {
            $stash['guest']=1;
        } else {
            $stash['guest']=0;
        }
        $app->render('html/download.html',$stash);
        exit;
    } else {
        $stash['error_message'] = "Error retrieving data file: file not found: $dat_file";
        $app->render('html/download_error.html',$stash);
        exit;
    }

});

$app->get('/initiateWebDownload/:udi', function ($udi) use ($app) {
    # this is called by a javascript function.  This route
    # sets up a symlink then produces output that replaces
    # the content of the "The dataset you selected is ready for download"
    # div with a download link (button) to the generated symlink.
    global $user;
    $env = $app->environment();
    if (!user_is_logged_in_somehow()) {
        drupal_exit();
    }
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];
    if(empty($user->name)) { $uid = uniqid($_SESSION['guestAuthUser'] . '_'); } else { $uid = uniqid($user->name . '_'); }
    $stash = array();
    $stash['server'] = $env['SERVER_NAME'];
    $stash['uid'] = $uid;
    $stash['udi'] = $udi;
    $stash["dataset_filename"]=$dataset['dataset_filename'];
    $tstamp=date('c');
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    
    $approved_md_udis=getApprovedMetadataUDIs(); 
    $dl_ok = 0;
    if( (isset($approved_md_udis["$udi"])) or (!(( isset($GLOBALS['griidc']['syswide']['enforce_approved_metadata'] ) and ( $GLOBALS['griidc']['syswide']['enforce_approved_metadata'] == 1 ))))) {
        $dl_ok = 1;
    } else {
        $dl_ok = 0;
    }

    if ($dataset['access_status'] != "Restricted" and $dataset['access_status'] != "Approval" and $dl_ok == 1) {
        $dat_file = $GLOBALS['griidc']['paths']['data_download']."/$dataset[udi]/$dataset[udi].dat";

        if ($GLOBALS['config']['DataDiscovery']['alternateDownloadSite'] == 1) {
            $host = $GLOBALS['config']['DataDiscovery']['alternateDownloadSiteServer'];
            $return = system("ssh apache@$host -C mkdir ".$GLOBALS['griidc']['paths']['http_download']."/$uid/"); 
            $return = system("ssh apache@$host -C ln -s $dat_file ".$GLOBALS['griidc']['paths']['http_download']."/$uid/$dataset[dataset_filename]");
            $stash['alternateDownloadSite']=1;
            $stash['alternateDownloadSiteServer']=$host;
            $altTag = " (ALT-SITE)";
            $stash['downloadUrl']="$protocol$host/download/$uid/$dataset[dataset_filename]";
            
            `echo "$tstamp\t$dat_file\t$uid$altTag" >> /var/log/griidc/downloads.log`;
            $app->render('html/download-file.html',$stash);
            exit;
        } else {
            if (file_exists($dat_file)) {
                mkdir($GLOBALS['griidc']['paths']['http_download']."/$uid/");
                symlink($dat_file,$GLOBALS['griidc']['paths']['http_download']."/$uid/$dataset[dataset_filename]");
                $altTag = '';
                $stash['downloadUrl']="$protocol$env[SERVER_NAME]/download/$uid/$dataset[dataset_filename]";
                # logging
                `echo "$tstamp\t$dat_file\t$uid$altTag" >> /var/log/griidc/downloads.log`;
                $app->render('html/download-file.html',$stash);
                exit;
            } else {
                print "Error";
                exit;
            }  
        }
    }
});

$app->get('/enableGridFTP/:udi', function ($udi) use ($app) {
    # this is called by a javascript function.  This route
    # sets up a hardlink for GridFTP then produces output that replaces
    # the content of the "The dataset you selected is ready for download"
    # div with GridFTP instructions. 
    global $user;
    if (!user_is_logged_in_somehow()) {
        drupal_exit();
    }
    $homedir = getHomedir($user->name);
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];

    if ($dataset['access_status'] != "Restricted" and $dataset['access_status'] != "Approval") {
           
    }

    $dat_file = $GLOBALS['griidc']['paths']['data_download']."/$dataset[udi]/$dataset[udi].dat";
    if (file_exists($dat_file)) {
        $env = $app->environment();
       
        $ds_symlink=$homedir."/download/$dataset[udi]/$dataset[dataset_filename]";
        # remove any existing potential stale symlink with the same name.
        if(file_exists($ds_symlink)) {
            unlink($ds_symlink);
        }
        
        if(!file_exists($homedir."/download/$dataset[udi]")) {
            mkdir ($homedir."/download/$dataset[udi]");
        }
    
        symlink($dat_file, $ds_symlink);
        $tstamp=date('c');
        $user_name = $user->name;
        # logging
        `echo "$tstamp\t$dat_file\t$user_name-GRIDFTP" >> /var/log/griidc/downloads.log`;
    }
    $stash['udi']=$dataset['udi'];
    $stash['dataset_filename']=$dataset['dataset_filename'];
    $app->render('html/gridftp.html',$stash);
    exit;
});

$app->get('/download_redirect/:udi', function ($udi) use ($app) {
    $stash['udi'] = $udi;
    $stash['final_destination'] = $app->request()->get('final_destination');
    $app->render('html/download_redirect.html',$stash);
    exit;
});

$app->run();

function getHashes($udi) {
    $sql = "select fs_md5_hash, fs_sha1_hash, fs_sha256_hash from registry_view where 
            dataset_udi = ?";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $raw_data = $data->fetch();
    $md5    = '';
    $sha1   = '';
    $sha256 = '';
    if ($raw_data) {
        $md5    = $raw_data[0];
        $sha1   = $raw_data[1];
        $sha256 = $raw_data[2];
    }
    return "$md5|$sha1|$sha256";
}

function getApprovedMetadataUDIs() {
    $hash = array();
    $sql = "select dataset_udi from registry_view where 
            metadata_status = 'Accepted'";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute();
    while ($raw_data = $data->fetch()) {
        $hash[$raw_data[0]]=1;
    }
    $dbms = null;
    return $hash;
}

function isMetadataApproved($udi) {
    $sql = "select count(*) from registry_view where 
            metadata_status = 'Accepted' and dataset_udi = ?;";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    while ($row = $data->fetch()) {
        $count = $row[0];
    }
    return ($count>0);
}
?>
