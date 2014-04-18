<?php
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
# local functions for the packaging sub-module to the data-discovery module
require_once 'lib/package.php';
# OpenID API for PHP
require_once '/usr/local/share/lightopenid-lightopenid/openid.php';
# GRIIDC database utilities
require_once '/usr/local/share/GRIIDC/php/db-utils.lib.php';

date_default_timezone_set('UTC');

function user_is_logged_in_somehow() {
    $drupal_login = user_is_logged_in();
    $alternate_login = (isset($_SESSION['gAuthLogin']) and $_SESSION['gAuthLogin']);
    if ($drupal_login or $alternate_login ) { return true; } else { return false; }
}

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
    drupal_add_js('/includes/geoviz/geoviz.js',array('type'=>'external'));
    $stash=index($app);
    # for now, only do this for guestAuthUser people, GoMRI auto-download is handled elsewhere.
    if( (isset($_COOKIE['dl_attempt_udi_cookie'])) and (isset($_SESSION['guestAuthUser'])) ) {
        $udi =  $_COOKIE['dl_attempt_udi_cookie'];
        unset($_COOKIE['dl_attempt_udi_cookie']);
        # remove cookie
        setcookie('dl_attempt_udi_cookie', "", time() - 3600, '/', $_SERVER['SERVER_NAME']);
        $env = $app->environment();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        # this is wrong here...  I need to load the main html then inject this into the div as used by gomri login
        $stash['download']=$udi;
        drupal_set_message("Guest access enabled for ".$_SESSION['guestAuthUser'],'status');
    }
    return $app->render('html/index.html',$stash);
});

// currently a work in progress...
$app->get('/guest-logout', function () use ($app) {
    try {
        $env = $app->environment();
        $_SESSION['guestAuthUser'] = null;
        unset($_SESSION['guestAuthUser']);
        drupal_set_message("Guess access has been logged out.",'status');
        $env = $app->environment();
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $baseUrl  = "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]";
        drupal_goto($baseUrl);
    } catch(ErrorException $e) {
        drupal_set_message($e->getMessage(),'error');
    }
});

$app->get('/google-auth', function () use ($app) {
    try {
        $env = $app->environment();
        $openid = new LightOpenID($env["SERVER_NAME"]);
        if(!$openid->mode) {
            if(isset($_GET['login'])) {
                $openid->identity = 'https://www.google.com/accounts/o8/id';
                header('Location: ' . $openid->authUrl());
            }
            $openid->identity = 'https://www.google.com/accounts/o8/id';
            $openid->required = array('contact/email', 'contact/country/home', 'namePerson/first', 'namePerson/last');
            drupal_goto($openid->authUrl());
        } else {
            $openid->validate();
            $info=$openid->getAttributes();
            $_SESSION['guestAuthUser'] = $info["contact/email"];
            $_SESSION['gAuthLogin']=true;
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $baseUrl  = "$protocol$env[SERVER_NAME]/$GLOBALS[PAGE_NAME]";
            drupal_goto($baseUrl);
        }
    } catch(ErrorException $e) {
        drupal_set_message($e->getMessage(),'error');
    }
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
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/logins.js",array('type'=>'external'));
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
    $stash = array();
    $stash['registered_datasets'] = array();
    $stash['identified_datasets'] = array();

    $reg_filters = array('dataset_download_status=done','registry_id!=00%');

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

    $unrestricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('restricted=0')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);

    foreach ($unrestricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset);
        $stash['unrestricted_datasets'][] = $dataset;
    }

    $restricted_datasets = get_registered_datasets(getDBH('GOMRI'),array_merge($reg_filters,array('restricted=1')),$filter,$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);

    foreach ($restricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset);
        $stash['restricted_datasets'][] = $dataset;
    }

    if (empty($geo_filter) or $geo_filter == 'undefined') {
        $identified_datasets = get_identified_datasets(getDBH('GOMRI'),array("$by=$id",'dataset_download_statuses!=done,RemotelyHosted','status=2'),$filter,$GLOBALS['config']['DataDiscovery']['identifiedOrderBy']);
        foreach ($identified_datasets as $dataset) {
            add_project_info($dataset);
            $stash['identified_datasets'][] = $dataset;
        }
    }

    $stash['filt'] = $filter;

    $app->render('html/datasets.html',$stash);
    exit;
})->conditions(array('filter' => '.*', 'by' => '.*', 'id' => '.*', 'geo_filter' => '.*'));

$app->get('/dataset_details/:udi', function ($udi) use ($app) {

    if (preg_match('/^00/',$udi)) {
        $stash['datasets'] = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $stash['datasets'] = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }

    $app->render('html/dataset_details.html',$stash);
    exit;  # prevents Drupal wrapper in output
});

$app->get('/package.*', function () use ($app) {
    global $user;
    if (empty($user->name)) {
        drupal_set_message("You must be logged in to download data.",'error');
        return $app->render('html/not_logged_in.html');
    }
    $app->pass();
});

$app->get('/package', function () use ($app) {
    $stash = array();
    $env = $app->environment();
    drupal_add_js("/$GLOBALS[PAGE_NAME]/js/package.js",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/datasets.css",array('type'=>'external'));
    drupal_add_css("/$GLOBALS[PAGE_NAME]/includes/css/dataset_details.css",array('type'=>'external'));
    $stash['defaultFilter'] = $app->request()->get('filter');
    $app->render('html/package.html',$stash);
});

$app->get('/package/add/:udi', function ($udi) use ($app) {
    $username = getDrupalUserName();
    addToPackage(getDBH('GOMRI'),$username,$udi);
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/remove/:udi', function ($udi) use ($app) {
    $username = getDrupalUserName();
    removeFromPackage(getDBH('GOMRI'),$username,$udi);
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/empty', function () use ($app) {
    $username = getDrupalUserName();
    emptyPackage(getDBH('GOMRI'),$username);
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/items', function () use ($app) {
    $username = getDrupalUserName();
    header('Content-type: application/json');
    echo packageToJSON(getDBH('GOMRI'),$username);
    exit;
});

$app->get('/package/datasets/:udis', function ($udis) use ($app) {
    $stash = array();
    $stash['registered_datasets'] = array();

    if ($udis != '') {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_ids=$udis"),'',$GLOBALS['config']['DataDiscovery']['registeredOrderBy']);
        foreach ($datasets as $dataset) {
            add_download_size($dataset);
            add_project_info($dataset);
            $stash['registered_datasets'][] = $dataset;
        }
    }

    $stash['package'] = true;
    $app->render('html/datasets.html',$stash);
    exit;
})->conditions(array('udis' => '.*'));

$app->get('/package/download/:udis', function ($udis) use ($app) {
    $datasets = null;

    if (empty($udis)) {
        drupal_set_message("Error: no datasets selected for download",'error');
        exit;
    }

    $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_ids=$udis"));

    $files = array();

    $zippath = '/tmp';
    $zipfile = gen_uuid() . '.zip';

    $zip = new ZipArchive();

    if ($zip->open("$zippath/$zipfile", ZIPARCHIVE::OVERWRITE)) {

        $error = false;

        foreach ($datasets as $dataset) {
            $dat_file = "/sftp/data/$dataset[udi]/$dataset[udi].dat";
            if (file_exists($dat_file)) {
                $zip->addFile($dat_file,"$dataset[udi]/$dataset[dataset_filename]");
            }
            else {
                drupal_set_message("Error creating zip: data file not found: $dat_file",'error');
                $error = true;
            }
            $met_file = "/sftp/data/$dataset[udi]/$dataset[udi].met";
            if (file_exists($met_file)) {
                $zip->addFile($met_file,"$dataset[udi]/$dataset[metadata_filename]");
            }
        }

        $zip->close();

        if ($error) { exit; };

        if (file_exists("$zippath/$zipfile")) {
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for i.e.
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:" . filesize("$zippath/$zipfile"));
            header("Content-Disposition: attachment; filename=$zipfile");
            readfile("$zippath/$zipfile");
            exit;
        }
        else {
            drupal_set_message("Error creating zip.",'error');
        }
    }
    else {
        drupal_set_message("Error opening zip file.",'error');
    }
});

$app->get('/metadata/:udi', function ($udi) use ($app) {
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
    $met_file = "/sftp/data/$dataset[udi]/$dataset[udi].met";
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
    global $user;
    if (!user_is_logged_in_somehow()) {
        #$stash['error_message'] = "You must be logged in to download datasets.";
        #$app->render('html/download_error.html',$stash);
        drupal_exit();
    }
    if (preg_match('/^00/',$udi)) {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $datasets = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }
    $dataset = $datasets[0];

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

    $dat_file = "/sftp/data/$dataset[udi]/$dataset[udi].dat";
    if (file_exists($dat_file)) {
        $env = $app->environment();
        $uid = 0;
        if(empty($user->name)) {
            $uid = uniqid($_SESSION['guestAuthUser'] . '_');
        } else {
            $uid = uniqid($user->name . '_');
        }
        mkdir("/sftp/download/$uid/");
        symlink($dat_file,"/sftp/download/$uid/$dataset[dataset_filename]");
        
        $stash = array();
        $stash['server'] = $env['SERVER_NAME'];
        $stash['uid'] = $uid;
        $stash['dataset'] = $dataset;
        $stash['bytes'] = filesize($dat_file);
        $stash['filesize'] = bytes2filesize($stash['bytes'],1);
        $stash['filt'] = $app->request()->get('filter');
        $tstamp=date('c');
        # logging
        `echo "$tstamp\t$dat_file\t$uid" >> downloadlog.txt`;
        $app->render('html/download.html',$stash);
        exit;
    }
    else {
        $stash['error_message'] = "Error retrieving data file: file not found: $dat_file";
        $app->render('html/download_error.html',$stash);
        exit;
    }
});

$app->get('/enableGridFTP/:udi', function ($udi) use ($app) {
    global $user;
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

    if ($dataset['access_status'] != "Restricted" and $dataset['access_status'] != "Approval") {
           
    }

    $dat_file = "/sftp/data/$dataset[udi]/$dataset[udi].dat";
    if (file_exists($dat_file)) {
        $env = $app->environment();
       
        # remove any existing potential stale hardlink with the same name.
        # WARNING: (limitation) If files requested by have the same name, the last one wins
        # because there is no requirement for uniqueness of user-named files.  We are
        # serving back the file with the name it was uploaded with.
        $ds_hardlink="/sftp/data/GridFTP/$user->name/$dataset[dataset_filename]";
        if(file_exists($ds_hardlink)) {
            unlink($ds_hardlink);
        }
        
        link($dat_file, $ds_hardlink);
        # Write a file dating this hardlink for later removal  (UNIX timestamp)
        $date = date("U"); # UNIXTIME 
        if (!(is_dir("/sftp/data/GridFTP-Status/$user->name"))) {
            mkdir("/sftp/data/GridFTP-Status/$user->name/");
        }
        $ds_hardlink_createdon="/sftp/data/GridFTP-Status/$user->name/$dataset[dataset_filename].createdon";
        file_put_contents($ds_hardlink_createdon,"$ds_hardlink|$date|".filesize($dat_file)."\n");
        $tstamp=date('c');
        $user_name = $user->name;
        # logging
        `echo "$tstamp\t$dat_file\t$user_name-GRIDFTP" >> downloadlog.txt`;
        echo "File has been enabled on GridFTP";
    } else {
        echo "It was not possible to enable this file on GridFTP";
    }
});

$app->get('/download_redirect/:udi', function ($udi) use ($app) {
    $stash['udi'] = $udi;
    $stash['final_destination'] = $app->request()->get('final_destination');
    $app->render('html/download_redirect.html',$stash);
    exit;
});

$app->run();

?>
