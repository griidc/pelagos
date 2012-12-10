<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';
require_once '/usr/local/share/GRIIDC/php/rpis.php';

require_once 'lib/tree.php';

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
    $stash['tree'] = $GLOBALS['config']['tree'];
    if ($app->request()->get('tree')) {
        $stash['tree'] = array_merge($stash['tree'],json_decode($app->request()->get('tree'),true));
    }
    header('Content-type: text/javascript');
    $app->render("js/$name.js",$stash);
    exit;
});

$app->get('/', function () use ($app) {
    $env = $app->environment();
    drupal_add_js('/includes/jstree/jquery.jstree.js',array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/js/tree.js",array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/jstree.css",array('type'=>'external'));
    return $app->render('index.html');
});

$app->get('/json/:type.json', function ($type) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
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
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
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
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['people'] = getPeopleDetails(getDBH('RPIS'),array("lastName=$letter%"));
    $app->render('json/researchers.json',$stash);
    exit;
});

$app->get('/json/in/:letter.json', function ($letter) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['institutions'] = getInstitutionDetails(getDBH('RPIS'),array("name=$letter%"));
    $app->render('json/institutions.json',$stash);
    exit;
});

$app->get('/json/:type/projects/fundSrc/:fundSrc.json', function ($type,$fundSrc) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
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

$app->get('/json/:type/projects/peopleId/:peopleId.json', function ($type,$peopleId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['projects'] = getProjectDetails(getDBH('RPIS'),array("peopleId=$peopleId"));
    $stash['peopleId'] = $peopleId;
    $app->render('json/projects.json',$stash);
    exit;
});

$app->get('/json/:type/projects/institutionId/:institutionId.json', function ($type,$institutionId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['projects'] = getProjectDetails(getDBH('RPIS'),array("institutionId=$institutionId"));
    $app->render('json/projects.json',$stash);
    exit;
});

$app->get('/json/:type/tasks/projectId/:projectId.json', function ($type,$projectId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['tasks'] = getTaskDetails(getDBH('RPIS'),array("projectID=$projectId"));
    $app->render('json/tasks.json',$stash);
    exit;
});

$app->get('/json/:type/tasks/projectId/peopleId/:projectId/:peopleId.json', function ($type,$projectId,$peopleId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['tasks'] = getTaskDetails(getDBH('RPIS'),array("projectID=$projectId","peopleId=$peopleId"));
    $app->render('json/tasks.json',$stash);
    exit;
});

$app->get('/json/:type/datasets/projectId/:projectId.json', function ($type,$projectId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['datasets'] = array(
                            array('Title'=>'Dataset 1','ID'=>'1'),
                            array('Title'=>'Dataset 2','ID'=>'2')
                         );
    $app->render('json/datasets.json',$stash);
    exit;
});

$app->get('/json/:type/datasets/taskId/:taskId.json', function ($type,$taskId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $stash['datasets'] = array(
                            array('Title'=>'Dataset 1','ID'=>'1'),
                            array('Title'=>'Dataset 2','ID'=>'2')
                         );
    $app->render('json/datasets.json',$stash);
    exit;
});

$app->run();

?>
