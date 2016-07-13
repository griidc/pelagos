<?php
// @codingStandardsIgnoreFile

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'Data Discovery';
$GLOBALS['pelagos']['show_title'] = false;

# load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

# load Common library from global share
require_once($GLOBALS['config']['paths']['share'].'/php/Common.php');

# check for local config file
if (file_exists('config.ini')) {
    # merge local config with global config
    $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
}

# load library info
$GLOBALS['libraries'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/libraries.ini', true);

# load database connection info
$GLOBALS['db'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/db.ini', true);

# add pelagos/share/php to the include path
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']['paths']['share'] . '/php');

# load Drupal functions
require_once 'drupal.php';
# load includes file dumper
require_once 'dumpIncludesFile.php';
# load RIS query functions
require_once 'rpis.php';
# load dataset query functions
require_once 'datasets.php';
# load database utilities
require_once 'DBUtils.php';
# load misc utilities and stuff...
require_once 'utils.php';
# load auth library
require_once 'auth.php';
# load LDAP functions
require_once 'ldap.php';

# local functions for data-discovery module
require_once 'lib/search.php';
# local functions for the packaging sub-module to the data-discovery module
//require_once 'lib/package.php';

date_default_timezone_set('UTC');

# add js library - informs drupal to add these standard js libraries upstream.
# can also use drupal_add_js to specify a full path to a js library to include.
# similarly, there is a drupal_add_css function.  These js includes are sent
# to the browser at the time drupal sends its own.  "system" is the main
# drupal module.
drupal_add_library('system', 'ui.tabs');

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

# add custom Twig extensions
$app->view->parserExtensions = array(
    new \Pelagos\TwigExtensions()
);

# define global variables for use in templates
$app->hook('slim.before', function () use ($app) {
    global $user;
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off' ||
        $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
    $app->view()->appendData(array('hostname' => $env['SERVER_NAME']));
    $app->view()->appendData(array('pageName' => preg_replace('/^\//', '', $env['SCRIPT_NAME'])));
    $app->view()->appendData(array('currentPage' => urlencode(preg_replace('/^\//', '', $_SERVER['REQUEST_URI']))));
    if (!empty($user->name)) {
        $app->view()->appendData(array('uid' => $user->name));
    }
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    header('Content-type: text/javascript');
    $stash['logged_in'] = (user_is_logged_in_somehow());
    $app->render("js/$name.js", $stash);
    drupal_exit();
});

$app->get('/css/:name.css', function ($name) use ($app) {
    header('Content-type: text/css');
    $app->render("css/$name.css");
    drupal_exit();
});

$app->get('/', function () use ($app) {
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/openlayers/2.13.1/OpenLayers.js', array('type'=>'external'));
    drupal_add_js('//maps.google.com/maps/api/js?v=3.21&sensor=false', array('type'=>'external'));
    drupal_add_js('/includes/geoviz/geoviz.js', array('type'=>'external'));
    $stash = index($app);
    # for now, only do this for guestAuthUser people, GoMRI auto-download is handled elsewhere.
    if (isset($_COOKIE['dl_attempt_udi_cookie']) and user_is_logged_in_somehow()) {
        $udi =  $_COOKIE['dl_attempt_udi_cookie'];
        unset($_COOKIE['dl_attempt_udi_cookie']);
        # remove cookie
        setcookie('dl_attempt_udi_cookie', "", time() - 3600, '/', $_SERVER['SERVER_NAME']);
        $env = $app->environment();
        $protocol = (!empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off' ||
            $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $stash['download'] = $udi;
        if (isset($_SESSION['guestAuthUser'])) {
            drupal_set_message("Guest access enabled for ".$_SESSION['guestAuthUser'], 'status');
        }
    }
    return $app->render('html/index.html', $stash);
});

$app->post('/', function () use ($app) {
    $stash = index($app);
    # regardless of user-friendly javascript warning elsewhere, this will disallow
    # unauthorized downloads server-side with a silent fail and reflow of the page.
    if (user_is_logged_in_somehow()) {
        $stash['download'] = $app->request()->post('download');
        $stash['srvr'] = "https://$_SERVER[HTTP_HOST]";
    }
    return $app->render('html/index.html', $stash);
});

$app->get('/datasets/:filter/:by/:id/:geo_filter', function ($filter, $by, $id, $geo_filter) use ($app) {
    $approvedMetadata = getApprovedMetadataUDIs();
    $stash = array();
    $stash['registered_datasets'] = array();
    $stash['identified_datasets'] = array();

    $reg_filters = array('registry_id!=00%');

    $RIS_DBH = openDB('RIS_RO');
    $GOMRI_DBH = openDB('GOMRI_RO');

    if (!empty($by)) {
        if ($by == 'otherSources') {
            $reg_filters[] = 'registry_id=00%';
        } elseif ($by == 'otherSource') {
            $reg_filters[] = "registry_id=00.x$id%";
        } else {
            if ($by != 'projectId') {
                if ($by == 'YR1') {
                    $filters = array("fundSrc>0","fundSrc<6");
                } else {
                    $filters = array("$by=$id");
                }
                $projects = getProjectDetails($RIS_DBH, $filters);
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
    if (isset($GLOBALS['config']['system']['enforce_approved_metadata']) and
        $GLOBALS['config']['system']['enforce_approved_metadata'] == 1) {
        $unrestricted_datasets = get_registered_datasets(
            $GOMRI_DBH,
            array_merge(
                $reg_filters,
                array(
                    'availability='.
                        'available'
                )
            ),
            $filter,
            $GLOBALS['config']['DataDiscovery']['registeredOrderBy']
        );
        $restricted_datasets = get_registered_datasets(
            $GOMRI_DBH,
            array_merge(
                $reg_filters,
                array(
                    'availability='.
                        'available_with_restrictions'
                )
            ),
            $filter,
            $GLOBALS['config']['DataDiscovery']['registeredOrderBy']
        );
        $md_under_review_datasets = get_registered_datasets(
            $GOMRI_DBH,
            array_merge(
                $reg_filters,
                array(
                    'availability='.
                        'unavailable_pending_metadata_acceptance'
                )
            ),
            $filter,
            $GLOBALS['config']['DataDiscovery']['registeredOrderBy']
        );
        $unavailable_datasets = get_identified_datasets(
            $GOMRI_DBH,
            array_merge(
                $reg_filters,
                array(
                    'availability='.
                        'unavailable_pending_metadata_submission,'.
                        'unavailable_pending_data_submission,'.
                        'unavailable_pending_registration'
                )
            ),
            $filter,
            $GLOBALS['config']['DataDiscovery']['identifiedOrderBy']
        );
    } else {
        $unrestricted_datasets = get_registered_datasets(
            $GOMRI_DBH,
            array_merge(
                $reg_filters,
                array(
                    'availability='.
                        'available,'.
                        'unavailable_pending_metadata_submission,'.
                        'unavailable_pending_metadata_acceptance',
                    'restricted=0'
                )
            ),
            $filter,
            $GLOBALS['config']['DataDiscovery']['registeredOrderBy']
        );
        $restricted_datasets = get_registered_datasets(
            $GOMRI_DBH,
            array_merge(
                $reg_filters,
                array(
                    'availability='.
                        'available_with_restrictions,'.
                        'unavailable_pending_metadata_submission,'.
                        'unavailable_pending_metadata_acceptance',
                    'restricted=1'
                )
            ),
            $filter,
            $GLOBALS['config']['DataDiscovery']['registeredOrderBy']
        );
        $md_under_review_datasets = array();
        $unavailable_datasets = get_identified_datasets(
            $GOMRI_DBH,
            array_merge(
                $reg_filters,
                array(
                    'availability='.
                    'unavailable_pending_data_submission,'.
                    'unavailable_pending_registration'
                )
            ),
            $filter,
            $GLOBALS['config']['DataDiscovery']['identifiedOrderBy']
        );
    }

    foreach ($unrestricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset, $RIS_DBH);
        $stash['unrestricted_datasets'][] = $dataset;
    }

    foreach ($restricted_datasets as $dataset) {
        add_download_size($dataset);
        add_project_info($dataset, $RIS_DBH);
        $stash['restricted_datasets'][] = $dataset;
    }

    foreach ($md_under_review_datasets as $dataset) {
        add_project_info($dataset, $RIS_DBH);
        add_download_size($dataset);
        $stash['md_under_review_datasets'][] = $dataset;
    }

    foreach ($unavailable_datasets as $dataset) {
        add_project_info($dataset, $RIS_DBH);
        $stash['identified_datasets'][] = $dataset;
    }

    $stash['filt'] = $filter;
    $app->render('html/datasets.html', $stash);
    drupal_exit();
})->conditions(array('filter' => '.*', 'by' => '.*', 'id' => '.*', 'geo_filter' => '.*'));

$app->get('/dataset_details/:udi', function ($udi) use ($app) {
    # used by a javascript that displays details on each
    # of the datasets in an expandable/collapsable manner
    $GOMRI_DBH = openDB('GOMRI_RO');
    if (preg_match('/^00/', $udi)) {
        $stash['datasets'] = get_registered_datasets($GOMRI_DBH, array("registry_id=$udi%"));
    } else {
        $stash['datasets'] = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));
    }

    $app->render('html/dataset_details.html', $stash);
    drupal_exit();  # prevents Drupal wrapper in output
});


$app->get('/metadata/:udi', function ($udi) use ($app) {
    $env = $app->environment();
    if (isMetadataApproved($udi)) {
        // if there is a file on disk, capture it
        $GOMRI_DBH = openDB('GOMRI_RO');
        if (preg_match('/^00/', $udi)) {
            $datasets = get_registered_datasets($GOMRI_DBH, array("registry_id=$udi%"));
        } else {
            $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));
        }
        $dataset = $datasets[0];

        $disk_metadata_file_mimetype = '';
        $disk_metadata_file = '';
        $met_file = '/' . $GLOBALS['config']['paths']['download'] . "/$dataset[udi]/$dataset[udi].met";
        if (file_exists($met_file)) {
            $info = finfo_open(FILEINFO_MIME_TYPE);
            $disk_metadata_file_mimetype = finfo_file($info, $met_file);
            $disk_metadata_file = file_get_contents($met_file);
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
                                        from registry_view
                                        where dataset_udi = ?
                                    )";

        $dbms = openDB("GOMRI_RO");
        $data = $dbms->prepare($sql);
        $data->execute(array($udi));
        $raw_data = $data->fetch();
        if ($raw_data) {
            # Serve it out from the data in the database by default
            # the following line is probably better done in SQL, so this will be changed in the near future
            $filename = preg_replace(
                array('/{/','/}/'),
                array('',''),
                $raw_data['filename']
            );
            $filename = preg_replace('/:/', '-', $filename);
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for i.e.
            header("Content-Type: text/xml");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:" . strlen($raw_data['metadata_xml']));
            header("Content-Disposition: attachment; filename=$filename");
            ob_clean();
            flush();
            print $raw_data['metadata_xml'];
            drupal_exit();
        } elseif (strlen($disk_metadata_file) > 0) {
            # Serve it out from the data in the filesystem if it wasn't in the database
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for i.e.
            header("Content-Type: $disk_metadata_file_mimetype");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:" . filesize($met_file));
            header("Content-Disposition: attachment; filename=$dataset[metadata_filename]");
            ob_clean();
            flush();
            readfile($met_file);
            drupal_exit();
        } else {
            drupal_set_message('Error retrieving metadata from database and filesystem.', 'error');
            drupal_goto(preg_replace('/^\//', '', $env['SCRIPT_NAME'])); # reload calling page
        }
    } else {
        drupal_set_message('Metadata that has not been approved cannot be downloaded.', 'error');
        drupal_goto(preg_replace('/^\//', '', $env['SCRIPT_NAME'])); # reload calling page
    }
})->conditions(array('udi' => '([A-Z][A-Z\d])\.x\d{3}\.\d{3}:\d{4}'));

$app->get('/metadata/', function () use ($app) {
    $files = array();
    $directories = array('GoMRI','Others');
    foreach ($directories as $directory) {
        $dir_files = get_accepted_metadata($directory);
        $max_last_modified_ts = strtotime('2010-04-20');
        foreach ($dir_files as $file) {
            if ($file['last_modified_datetime'] != '{}') {
                $last_modified_ts = strtotime(
                    preg_replace(
                        array('/{/','/}/'),
                        array('',''),
                        $file['last_modified_datetime']
                    )
                );
            } else {
                $last_modified_ts = strtotime(
                    preg_replace(
                        array('/{/','/}/'),
                        array('',''),
                        $file['last_modified_date']
                    )
                );
            }
            if ($last_modified_ts > $max_last_modified_ts) {
                $max_last_modified_ts = $last_modified_ts;
            }
        }
        array_push(
            $files,
            array(
                'name' => "$directory/",
                'type' => 'folder',
                'last_modified' => strftime('%d-%b-%Y %H:%M', $max_last_modified_ts)
            )
        );
    }
    $stash = array('directory' => 'metadata',
                   'parent' => '',
                   'filename_max_len' => 24,
                   'files' => $files
             );
    $app->render('html/waf.html', $stash);
    drupal_exit();
});

$app->get('/metadata/:directory/', function ($directory) use ($app) {
    $files = get_accepted_metadata($directory);
    $filename_max_len = 0;
    for ($i=0; $i<count($files); $i++) {
        $files[$i]['name'] = preg_replace('/:/', '-', $files[$i]['dataset_udi']) . '-metadata.xml';
        $files[$i]['type'] = 'text';
        $size = strlen($files[$i]['metadata_xml']);
        if ($size > 1024000) {
            $size = round($size / 1024 / 1024);
            $size .= 'M';
        } elseif ($size > 999) {
            $size = round($size / 1024);
            $size .= 'K';
        } else {
            $size .= ' ';
        }
        $files[$i]['size'] = sprintf('% 4s', $size);
        if ($files[$i]['last_modified_datetime'] != '{}') {
            $last_modified_ts = strtotime(
                preg_replace(
                    array('/{/','/}/'),
                    array('',''),
                    $files[$i]['last_modified_datetime']
                )
            );
        } else {
            $last_modified_ts = strtotime(
                preg_replace(
                    array('/{/','/}/'),
                    array('',''),
                    $files[$i]['last_modified_date']
                )
            );
        }
        $files[$i]['last_modified'] = strftime('%d-%b-%Y %H:%M', $last_modified_ts);
        if (strlen($files[$i]['name']) > $filename_max_len) {
            $filename_max_len = strlen($files[$i]['name']) + 1;
        }
    }
    if ($filename_max_len < 24) {
        $filename_max_len = 24;
    }
    $stash = array('directory' => "metadata/$directory",
                   'parent' => '/metadata',
                   'filename_max_len' => $filename_max_len,
                   'files' => $files
             );
    $app->render('html/waf.html', $stash);
    drupal_exit();
})->conditions(array('directory' => 'GoMRI|Others'));

$app->get('/metadata/:directory/:file', function ($directory, $file) use ($app) {
    $udi = preg_replace('/-metadata.xml$/', '', $file);
    $udi = preg_replace('/-/', ':', $udi);
    $dbms = openDB("GOMRI_RO");
    $SQL = 'SELECT dataset_udi,metadata_xml '.
        'FROM registry_view '.
        'JOIN metadata ON metadata.registry_id = registry_view.registry_id '.
        'WHERE metadata_status = \'Accepted\' AND dataset_udi = ?';
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
    $GOMRI_DBH = openDB('GOMRI_RO');
    if (preg_match('/^00/', $udi)) {
        $datasets = get_registered_datasets($GOMRI_DBH, array("registry_id=$udi%"));
    } else {
        $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));
    }
    $dataset = $datasets[0];
    $stash['dataset'] = $dataset;
    $app->render('html/download-external.html', $stash);
    drupal_exit();
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
    $GOMRI_DBH = openDB('GOMRI_RO');
    if (preg_match('/^00/', $udi)) {
        $datasets = get_registered_datasets($GOMRI_DBH, array("registry_id=$udi%"));
    } else {
        $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));
    }
    $dataset = $datasets[0];

    if (isset($dataset['metadata_filename']) ) {
        $dataset['metadata_filename'] = preg_replace('/:/', '-', $dataset['udi']) . '-metadata.xml';
    }

    list($fs_hash_md5, $fs_hash_sha1, $fs_hash_sha256) = preg_split("/\|/", getHashes($udi));

    if ($dataset['access_status'] == "Restricted") {
        $stash['error_message'] = "This dataset is restricted for author use only.";
        $app->render('html/download_error.html', $stash);
        drupal_exit();
    }

    if ($dataset['access_status'] == "Approval") {
        $stash['error_message'] = "This dataset can only be downloaded with author approval.";
        $app->render('html/download_error.html', $stash);
        drupal_exit();
    }

    $approved_md_udis = getApprovedMetadataUDIs();
    $dl_ok = 0;
    if (isset($approved_md_udis["$udi"]) or
        !(isset($GLOBALS['config']['system']['enforce_approved_metadata']) and
            $GLOBALS['config']['system']['enforce_approved_metadata'] == 1
        )
    ) {
        $dl_ok = 1;
    } else {
        $dl_ok = 0;
    }
    if ($dl_ok == 0) {
        $stash['error_message'] = "This dataset can not be downloaded because its metadata has not been approved.";
        $app->render('html/download_error.html', $stash);
        drupal_exit();
    }

    $dat_file = $GLOBALS['config']['paths']['data_download']."/$dataset[udi]/$dataset[udi].dat";

    $env = $app->environment();
    $stash = array();
    $stash['server'] = $env['SERVER_NAME'];
    $stash['dataset'] = $dataset;
    $stash['bytes'] = 0;
    if (file_exists($dat_file)) {
        $stash['bytes'] = filesize($dat_file);
    }
    $stash['filesize'] = bytes2filesize($stash['bytes'], 1);
    $stash['filt'] = $app->request()->get('filter');
    $stash['fs_hash_md5'] = $fs_hash_md5;
    $stash['fs_hash_sha1'] = $fs_hash_sha1;
    $stash['fs_hash_sha256'] = $fs_hash_sha256;

    if ($GLOBALS['config']['DataDiscovery']['alternateDownloadSite'] == 1) {
        $host = $GLOBALS['config']['DataDiscovery']['alternateDownloadSiteServer'];
        $return = exec("ssh apache@$host -C ls $dat_file");
        if (preg_match("/$dataset[udi].dat/", $return)) {
            if (isset($_SESSION['guestAuthUser']) and $_SESSION['guestAuthUser']) {
                $stash['guest'] = 1;
            } else {
                $stash['guest'] = 0;
            }
            $app->render('html/download.html', $stash);
        } else {
            $stash['error_message'] = "Error retrieving data file: file not found (on $host): $dat_file";
            $app->render('html/download_error.html', $stash);
        }
        drupal_exit();
    } elseif (file_exists($dat_file)) {
        if (isset($_SESSION['guestAuthUser']) and $_SESSION['guestAuthUser'] == true) {
            $stash['guest'] = 1;
        } else {
            $stash['guest'] = 0;
        }
        $stash['gridOK'] = canHazGridFTP($user, $udi, $dataset['dataset_filename']);
        $app->render('html/download.html', $stash);
        drupal_exit();
    } else {
        $stash['error_message'] = "Error retrieving data file: file not found: $dat_file";
        $app->render('html/download_error.html', $stash);
        drupal_exit();
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
    $GOMRI_DBH = openDB('GOMRI_RO');
    if (preg_match('/^00/', $udi)) {
        $datasets = get_registered_datasets($GOMRI_DBH, array("registry_id=$udi%"));
    } else {
        $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));
    }
    $dataset = $datasets[0];
    if (empty($user->name)) {
        $uid = uniqid($_SESSION['guestAuthUser'] . '_');
    } else {
        $uid = uniqid($user->name . '_');
    }
    $stash = array();
    $stash['server'] = $env['SERVER_NAME'];
    $stash['uid'] = $uid;
    $stash['udi'] = $udi;
    $stash["dataset_filename"] = $dataset['dataset_filename'];
    $tstamp = date('c');
    $protocol = (!empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off' ||
        $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $approved_md_udis = getApprovedMetadataUDIs();
    $dl_ok = 0;
    if (isset($approved_md_udis["$udi"]) or
        !(isset($GLOBALS['config']['system']['enforce_approved_metadata'])
            and $GLOBALS['config']['system']['enforce_approved_metadata'] == 1
        )
    ) {
        $dl_ok = 1;
    } else {
        $dl_ok = 0;
    }

    if ($dataset['access_status'] != "Restricted" and $dataset['access_status'] != "Approval" and $dl_ok == 1) {
        $dat_file = $GLOBALS['config']['paths']['data_download'] . "/$dataset[udi]/$dataset[udi].dat";

        if ($GLOBALS['config']['DataDiscovery']['alternateDownloadSite'] == 1) {
            $host = $GLOBALS['config']['DataDiscovery']['alternateDownloadSiteServer'];
            $return = system(
                "ssh apache@$host -C mkdir " .
                $GLOBALS['config']['paths']['http_download'] .
                "/$uid/"
            );
            $return = system(
                "ssh apache@$host -C ln -s $dat_file " .
                $GLOBALS['config']['paths']['http_download'] .
                "/$uid/$dataset[dataset_filename]"
            );
            $stash['alternateDownloadSite'] = 1;
            $stash['alternateDownloadSiteServer'] = $host;
            $altTag = " (ALT-SITE)";
            $stash['downloadUrl'] = "$protocol$host/download/$uid/" . rawurlencode($dataset['dataset_filename']);

            `echo "$tstamp\t$dat_file\t$uid$altTag" >> /var/log/griidc/downloads.log`;
            $app->render('html/download-file.html', $stash);
            drupal_exit();
        } else {
            if (file_exists($dat_file)) {
                mkdir($GLOBALS['config']['paths']['http_download']."/$uid/");
                symlink($dat_file, $GLOBALS['config']['paths']['http_download'] . "/$uid/$dataset[dataset_filename]");
                $altTag = '';
                $stash['downloadUrl'] = "$protocol$env[SERVER_NAME]/download/$uid/" .
                    rawurlencode($dataset['dataset_filename']);
                # logging
                `echo "$tstamp\t$dat_file\t$uid$altTag" >> /var/log/griidc/downloads.log`;
                $app->render('html/download-file.html', $stash);
                drupal_exit();
            } else {
                print "Error";
                drupal_exit();
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
    $GOMRI_DBH = openDB('GOMRI_RO');
    if (preg_match('/^00/', $udi)) {
        $datasets = get_registered_datasets($GOMRI_DBH, array("registry_id=$udi%"));
    } else {
        $datasets = get_identified_datasets($GOMRI_DBH, array("udi=$udi"));
    }
    $dataset = $datasets[0];

    if ($dataset['access_status'] != "Restricted" and $dataset['access_status'] != "Approval") {
    }

    $dat_file = $GLOBALS['config']['paths']['data_download'] . "/$dataset[udi]/$dataset[udi].dat";
    if (file_exists($dat_file)) {
        $env = $app->environment();

        $ds_symlink = "$homedir/download/$dataset[udi]/$dataset[dataset_filename]";
        # remove any existing potential stale symlink with the same name.
        if (file_exists($ds_symlink)) {
            unlink($ds_symlink);
        }

        if (!file_exists("$homedir/download/$dataset[udi]")) {
            mkdir("$homedir/download/$dataset[udi]");
        }

        symlink($dat_file, $ds_symlink);
        $tstamp = date('c');
        $user_name = $user->name;
        # logging
        `echo "$tstamp\t$dat_file\t$user_name-GRIDFTP" >> /var/log/griidc/downloads.log`;
    }
    $stash['udi'] = $dataset['udi'];
    $stash['dataset_filename'] = $dataset['dataset_filename'];
    $app->render('html/gridftp.html', $stash);
    drupal_exit();
});

$app->get('/download_redirect/:udi', function ($udi) use ($app) {
    $stash['udi'] = $udi;
    $stash['final_destination'] = $app->request()->get('final_destination');
    $app->render('html/download_redirect.html', $stash);
    drupal_exit();
});

$app->run();
