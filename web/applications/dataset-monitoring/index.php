<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';
require_once '/usr/local/share/GRIIDC/php/rpis.php';

require_once 'lib/dm.php';

$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['TwigView']['twigDirectory'];

require_once 'lib/Twig_Extensions_GRIIDC.php';

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    header('Content-type: text/javascript');
    $app->render("js/$name.js");
    exit;
});

$app->get('/', function () use ($app) {
    $env = $app->environment();
    drupal_add_js('/includes/jstree/jquery.jstree.js',array('type'=>'external'));
    drupal_add_js('/includes/tinyscrollbar/jquery.tinyscrollbar.min.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.events.js',array('type'=>'external'));
    drupal_add_js('/includes/mutate/mutate.min.js',array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/js/dm.js",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/jstree.css",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/scrollbars.css",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/projects.css",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/dataset_details.css",array('type'=>'external'));
    return $app->render('html/index.html');
});

$app->get('/json/:type.json', function ($type) use ($app) {
    switch ($type) {
        case 'in':
            $stash['institutions'] = getInstitutionDetails(getDBH('RPIS'));
            $app->render('json/institutions.json',$stash);
            break;
        case 're':
            $stash['letters'] = range('A','Z');
            $stash['letters'][] = 'Ö';
            $app->render('json/letters.json',$stash);
            break;
        case 'ra':
            $fundFilter = array('fundId>6');
            if (isset($GLOBALS['config']['exclude']['funds'])) {
                foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                    $fundFilter[] = "fundId!=$exclude";
                }
            }
            $stash['RFPS'] = getFundingSources(getDBH('RPIS'),$fundFilter);
            $app->render('json/research_awards.json',$stash);
            break;
    }
    exit;
});

$app->get('/json/ra/YR1.json', function () use ($app) {
    $fundFilter = array('fundId>0','fundId<7');
    if (isset($GLOBALS['config']['exclude']['funds'])) {
        foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
            $fundFilter[] = "fundId!=$exclude";
        }
    }
    $stash['YR1'] = getFundingSources(getDBH('RPIS'),$fundFilter);
    $app->render('json/YR1.json',$stash);
    exit;
});

$app->get('/json/re/:letter.json', function ($letter) use ($app) {
    $stash['people'] = getPeopleDetails(getDBH('RPIS'),array("lastName=$letter%"));
    $app->render('json/researchers.json',$stash);
    exit;
});

$app->get('/json/:type/projects/fundSrc/:fundSrc.json', function ($type,$fundSrc) use ($app) {
    $projectFilter = array("fundSrc=$fundSrc");
    if (isset($GLOBALS['config']['exclude']['projects'])) {
        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
            $projectFilter[] = "projectId!=$exclude";
        }
    }
    $stash['projects'] = getProjectDetails(getDBH('RPIS'),$projectFilter);
    $app->render('json/projects.json',$stash);
    exit;
});

$app->get('/projects/:by/:id', function ($by,$id) use ($app) {
    $stash['timestamp'] = date('Y-m-d g:i A (T)',time());
    if ($by == 'YR1') {
        $fundFilter = array('fundId>0','fundId<7');
        if (isset($GLOBALS['config']['exclude']['funds'])) {
            foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                $fundFilter[] = "fundId!=$exclude";
            }
        }
        $stash['funds'] = getFundingSources(getDBH('RPIS'),$fundFilter);
        for ($i=0; $i<count($stash['funds']); $i++) {
            $projectFilter = array('fundSrc='.$stash['funds'][$i]['ID']);
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $stash['funds'][$i]['projects'] = getTasksAndDatasets(getProjectDetails(getDBH('RPIS'),$projectFilter));
        }
        $app->render('html/YR1.html',$stash);
    }
    else {
        $stash['header'] = 'Datasets for: ';
        switch ($by) {
            case 'fundSrc':
                $funds = getFundingSources(getDBH('RPIS'),array("fundId=$id"));
                $stash['header'] .= $funds[0]['Name'];
                break;
            case 'peopleId':
                $people = getPeopleDetails(getDBH('RPIS'),array("peopleId=$id"));
                $stash['header'] .= $people[0]['FirstName'] . ' ' . $people[0]['LastName'];
                $stash['instName'] = $people[0]['Institution_Name'];
                break;
            case 'institutionId':
                $inst = getInstitutionDetails(getDBH('RPIS'),array("institutionId=$id"));
                $stash['header'] .= $inst[0]['Name'];
                break;
            case 'projectId':
                $proj = getProjectDetails(getDBH('RPIS'),array("projectId=$id"));
                $stash['header'] .= $proj[0]['Title'];
                break;
        }
        $projectFilter = array("$by=$id");
        if (isset($GLOBALS['config']['exclude']['projects'])) {
            foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                $projectFilter[] = "projectId!=$exclude";
            }
        }
        $stash['projects'] = getTasksAndDatasets(getProjectDetails(getDBH('RPIS'),$projectFilter));
        if ($app->request()->get('pdf')) {
            $env = $app->environment();
            header("Content-Type: text/html; charset=utf-8");
            $stash['pdf'] = true;
            $app->render('html/pdf.html',$stash);
        }
        else {
            $stash['by'] = $by;
            $stash['id'] = $id;
            $app->render('html/projects.html',$stash);
        }
    }
    exit;
});

$app->get('/dataset_details/:udi', function ($udi) use ($app) {
    $dbh = getDBH('GOMRI');

    $SELECT = 'SELECT
               title,
               status,
               dataset_uid,
               d.dataset_udi AS udi,
               abstract,
               CASE WHEN registry_id IS NULL THEN 0 ELSE 1 END AS registered';

    $FROM = 'FROM datasets d
             LEFT OUTER JOIN registry r ON r.dataset_udi = d.dataset_udi';

    $WHERE = "WHERE d.dataset_udi='$udi'";

    $stmt = $dbh->prepare("$SELECT $FROM $WHERE;");
    $stmt->execute();
    $stash['datasets'] = $stmt->fetchAll();

    $app->render('html/dataset_details.html',$stash);
    exit;
});

$app->get('/pdf/:by/:id/:name', function ($by,$id,$name) use ($app) {
    if ($by == 'YR1') {
        drupal_set_message("You cannot download a pdf for all Year One Block Grants. Please download each block grant individually.",'error');
        return;
    }
    $content = file_get_contents("http://localhost/dm/projects/$by/$id?pdf=true");

    require_once '/usr/local/share/dompdf/dompdf_config.inc.php';

    $dompdf = new DOMPDF();
    $dompdf->load_html($content);
    $dompdf->set_paper('letter', 'portrait');
    $dompdf->render();
    $dompdf->stream($name, array("Attachment" => true));

    exit;
});

$app->run();

?>
