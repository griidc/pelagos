<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';

require_once 'lib/rpis.php';
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
    $app->view()->appendData(array('baseUrl' => $env['SCRIPT_NAME']));
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
            $stash['RFPS'] = getFundingSources(getDBH('RPIS'),array('fundId>6'));
            $app->render('json/research_awards.json',$stash);
            break;
    }
    exit;
});

$app->get('/json/ra/YR1.json', function () use ($app) {
    $stash['YR1'] = getFundingSources(getDBH('RPIS'),array('fundId>0','fundId<6'));
    $app->render('json/YR1.json',$stash);
    exit;
});

$app->get('/json/re/:letter.json', function ($letter) use ($app) {
    $stash['people'] = getPeopleDetails(getDBH('RPIS'),array("lastName=$letter%"));
    $app->render('json/researchers.json',$stash);
    exit;
});

$app->get('/json/:type/projects/fundSrc/:fundSrc.json', function ($type,$fundSrc) use ($app) {
    $stash['projects'] = getProjectDetails(getDBH('RPIS'),array("fundSrc=$fundSrc"));
    $app->render('json/projects.json',$stash);
    exit;
});

$app->get('/projects/:by/:id', function ($by,$id) use ($app) {
    if ($by == 'YR1') {
        $funds = getFundingSources(getDBH('RPIS'),array('fundId>=2','fundId<=6'));
        foreach ($funds as $fund) {
            echo "<hr style='margin-top:2em;'><h3 style='margin-top:0; font-size=110%;'>$fund[Name]</h3>";
            $stash['projects'] = getTasksAndDatasets(getProjectDetails(getDBH('RPIS'),array("fundSrc=$fund[ID]")));
            $app->render('html/projects.html',$stash);
        }
    }
    else {
        $stash['projects'] = getTasksAndDatasets(getProjectDetails(getDBH('RPIS'),array("$by=$id")));
        $app->render('html/projects.html',$stash);
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

$app->run();

?>
