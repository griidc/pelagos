<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';
require_once '/usr/local/share/GRIIDC/php/rpis.php';
require_once '/usr/local/share/GRIIDC/php/datasets.php';

require_once 'lib/search.php';
require_once 'lib/package.php';

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
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
    $app->view()->appendData(array('currentPage' => urlencode(preg_replace('/^\//','',$_SERVER['REQUEST_URI']))));
    if (!empty($user->name)) {
        $app->view()->appendData(array('uid' => $user->name));
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
    $env = $app->environment();
    drupal_add_js('/includes/tinyscrollbar/jquery.tinyscrollbar.min.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.events.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.min.js',array('type'=>'external'));
    drupal_add_js('/tree/js/tree.js',array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/js/search.js",array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/js/package.js",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/css/search.css",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/scrollbars.css",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/datasets.css",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/dataset_details.css",array('type'=>'external'));
    return $app->render('html/index.html');
});

$app->get('/datasets/:filter/:by/:id', function ($filter,$by,$id) use ($app) {
    $stash = array();
    $stash['registered_datasets'] = array();
    $stash['other_datasets'] = array();
    $stash['identified_datasets'] = array();
    
    if (empty($by) or $by == 'otherSources' or $by == 'otherSource') {
        if ($by == 'otherSource') {
            $other_datasets = get_registered_datasets(getDBH('GOMRI'),array("filter=%$filter%","registry_id=00.x$id%",'dataset_download_status=done'),'year,title');
        }
        else {
            $other_datasets = get_registered_datasets(getDBH('GOMRI'),array("filter=%$filter%",'registry_id=00%','dataset_download_status=done'),'year,title');
        }
        foreach ($other_datasets as $dataset) {
            add_download_size($dataset);
            // hard-coded kluge for NODC data (needs refactoring once we have a table of organizations for other datasets)
            if (preg_match('/^00.x000/',$dataset['registry_id'])) {
                $dataset['organization'] = 'National Oceanographic Data Center';
            }
            $stash['other_datasets'][] = $dataset;
        }
    }

    if ($by != 'otherSources' and $by != 'otherSource') {
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

        $registered_datasets = get_registered_datasets(getDBH('GOMRI'),array("filter=%$filter%","$by=$id",'registry_id!=00%','dataset_download_status=done'));
        foreach ($registered_datasets as $dataset) {
            add_project_info($dataset);
            add_download_size($dataset);
            $stash['registered_datasets'][] = $dataset;
        }

        usort($stash['registered_datasets'],'sort_by_pi_year_title');

        $identified_datasets = get_identified_datasets(getDBH('GOMRI'),array("filter=%$filter%","$by=$id",'registered=0','status=2'));
        foreach ($identified_datasets as $dataset) {
            add_project_info($dataset);
            $stash['identified_datasets'][] = $dataset;
        }
    }

    $app->render('html/datasets.html',$stash);
    exit;
})->conditions(array('filter' => '.*', 'by' => '.*', 'id' => '.*'));

$app->get('/dataset_details/:udi', function ($udi) use ($app) {

    if (preg_match('/^00/',$udi)) {
        $stash['datasets'] = get_registered_datasets(getDBH('GOMRI'),array("registry_id=$udi%"));
    }
    else {
        $stash['datasets'] = get_identified_datasets(getDBH('GOMRI'),array("udi=$udi"));
    }

    $app->render('html/dataset_details.html',$stash);
    exit;
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
    drupal_add_js("$env[SCRIPT_NAME]/js/package.js",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/datasets.css",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/dataset_details.css",array('type'=>'external'));

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
    $stash['other_datasets'] = array();

    $udi_list = preg_split('/,/',$udis);
    $datasets_udis = implode(',',preg_grep('/^00/',$udi_list,PREG_GREP_INVERT));
    $other_datasets_udis = implode(',',preg_grep('/^00/',$udi_list));

    if ($datasets_udis != '') {
        $datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_ids=$datasets_udis"));
        foreach ($datasets as $dataset) {
            add_project_info($dataset);
            add_download_size($dataset);
            $stash['registered_datasets'][] = $dataset;
        }
    }

    usort($stash['registered_datasets'],'sort_by_pi_year_title');

    if ($other_datasets_udis != '') {
        $other_datasets = get_registered_datasets(getDBH('GOMRI'),array("registry_ids=$other_datasets_udis"));
        foreach ($other_datasets as $dataset) {
            add_download_size($dataset);
            // hard-coded kluge for NODC data (needs refactoring once we have a table of organizations for other datasets)
            if (preg_match('/^00.x000/',$dataset['registry_id'])) {
                $dataset['organization'] = 'National Oceanographic Data Center';
            }
            $stash['other_datasets'][] = $dataset;
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
            else {
                drupal_set_message("Error creating zip: metadata file not found: $met_file",'error');
                $error = true;
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
    }
    else {
        drupal_set_message("Error retrieving metadata file: file not found: $met_file",'error');
    }
});

$app->run();

?>
