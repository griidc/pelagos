<?php
// @codingStandardsIgnoreFile

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'Dataset Monitoring';

set_include_path("../../../share/php" . PATH_SEPARATOR . get_include_path());

# load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
# load Common lib
require_once 'Common.php';
# load local overrides and additions
$GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
# load library info
$GLOBALS['libraries'] = parse_ini_file($GLOBALS['config']['paths']['conf'].'/libraries.ini', true);
# load database connection info
$GLOBALS['db'] = parse_ini_file($GLOBALS['config']['paths']['conf'].'/db.ini', true);

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

# load dataset monitoring library
require_once 'lib/dm.php';

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

# add custom Twig extensions
$app->view->parserExtensions = array(
    new \Pelagos\TwigExtensions()
);

# define baseUrl for use in templates
$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
                 || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
    $app->view()->appendData(array('serverUrl' => "$protocol$env[SERVER_NAME]"));
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    $RIS_DBH = openDB('RIS_RO');
    $stash['funds'] = getFundingSources($RIS_DBH);
    $stash['projects'] = getProjectDetails($RIS_DBH, array("fundsrc=7"));
    $RIS_DBH = null;
    header('Content-type: text/javascript');
    $app->render("js/$name.js", $stash);
    exit;
});

$app->get('/css/:name.css', function ($name) use ($app) {
    header('Content-type: text/css');
    $app->render("css/$name.css");
    exit;
});

$app->get('/', function () use ($app) {
    $env = $app->environment();
    drupal_add_js('/includes/jquery-hashchange/jquery.ba-hashchange.min.js', array('type'=>'external'));
    drupal_add_js('/includes/tablesorter/js/jquery.tablesorter.min.js', array('type'=>'external'));
    drupal_add_js('/tree/js/tree.js', array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.min.js', array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.stack.min.js', array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.resize.min.js', array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/d3/3.5.3/d3.min.js', array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/js/dm.js", array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/js/dotgraph.js", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/dm.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/scrollbars.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/css/projects.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/dataset_details.css", array('type'=>'external'));
    return $app->render('html/index.html');
});

$app->get('/summaryCount/:projectId', function ($projectId) use ($app) {
    $database    = openDB("GOMRI_RO");
    $available   = count_registered_datasets($database, array("projectid=$projectId",'availability=available'));
    $registered  = count_registered_datasets($database, array("projectid=$projectId",'has_data=true'));
    $identified  = count_identified_datasets($database, array("projectid=$projectId",'status=2'));
    $database    = null;

    $raw = array(array(
                array( 'data' =>
                    array(
                        array(
                            $available,0
                        )
                    ),
                    'label' => 'Availiable'
                ),
                array( 'data' =>
                    array(
                        array(
                            $registered-$available,0
                        )
                    ),
                    'label' => 'Registered'
                ),
                array( 'data' =>
                    array(
                        array(
                            $identified-$registered,0
                        )
                    ),
                    'label' => 'Identified'
                )
            ),$projectId);

    print json_encode($raw);
    exit;
});

$app->get('/projects/:by/:id(/:renderer)', function ($by, $id, $renderer = 'browser') use ($app) {
    $stash['timestamp'] = date('Y-m-d g:i A (T)', time());
    $RIS_DBH = openDB('RIS_RO');
    if ($by == 'YR1') {
        $fundFilter = array('fundId>0','fundId<7');
        if (isset($GLOBALS['config']['exclude']['funds'])) {
            foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                $fundFilter[] = "fundId!=$exclude";
            }
        }
        $stash['funds'] = getFundingSources($RIS_DBH, $fundFilter);
        for ($i=0; $i<count($stash['funds']); $i++) {
            $projectFilter = array('fundSrc='.$stash['funds'][$i]['ID']);
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $stash['funds'][$i]['projects'] = getProjectsAndDatasets(getProjectDetails($RIS_DBH, $projectFilter));
        }
        $app->render('html/YR1.html', $stash);
    } else {
        switch ($by) {
            case 'fundSrc':
                $funds = getFundingSources($RIS_DBH, array("fundId=$id"));
                $stash['header'] = $funds[0]['Name'];
                $stash['pdfFilename'] = filterFilename('Dataset Monitoring - '.$funds[0]['Abbr']);
                break;
            case 'peopleId':
                $people = getPeopleDetails($RIS_DBH, array("peopleId=$id"));
                $stash['header'] = $people[0]['FirstName'] . ' ' . $people[0]['LastName'];
                $stash['instName'] = $people[0]['Institution_Name'];
                $stash['pdfFilename'] = filterFilename('Dataset Monitoring - '.$people[0]['FirstName'] .
                    ' ' . $people[0]['LastName'].' ('.$people[0]['Institution_Name'].')');
                break;
            case 'institutionId':
                $inst = getInstitutionDetails($RIS_DBH, array("institutionId=$id"));
                $stash['header'] = $inst[0]['Name'];
                $stash['pdfFilename'] = filterFilename('Dataset Monitoring - '.$inst[0]['Name']);
                break;
            case 'projectId':
                $proj = getProjectDetails($RIS_DBH, array("projectId=$id"));
                $stash['header'] = $proj[0]['Title'];
                $matches = array();
                $has_shorthand = preg_match('/\(.*\)/',$proj[0]['Title'],$matches);
                if ($has_shorthand) {
                    $consortia = array_pop($matches);
                    $stash['pdfFilename'] = "Dataset Monitoring - ".$proj[0]["Fund_Abbr"]." - $consortia";
                } else {
                    $words = array_slice(explode(' ', $stash['header'] = $proj[0]['Title'], 7),0,6);
                    $stash['pdfFilename'] = filterFilename("Dataset Monitoring - ".$proj[0]["Fund_Abbr"].'- '.implode(' ',$words));
                }
                break;
        }
        $projectFilter = array("$by=$id");
        if (isset($GLOBALS['config']['exclude']['projects'])) {
            foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                $projectFilter[] = "projectId!=$exclude";
            }
        }
        $stash['projects'] = getProjectsAndDatasets(getProjectDetails($RIS_DBH, $projectFilter));
        if ($renderer == 'dompdf') {
            $env = $app->environment();
            header("Content-Type: text/html; charset=utf-8");
            $stash['pdf'] = true;
            $stash['hostUrl'] = 'https://data.gulfresearchinitiative.org';
            $app->render('html/pdf.html', $stash);
        } elseif ($renderer == 'html2pdf') {
            $env = $app->environment();
            header("Content-Type: text/html; charset=utf-8");
            $stash['pdf'] = true;
            $stash['hostUrl'] = 'https://data.gulfresearchinitiative.org';
            $app->render('html/pdf.html', $stash);
        } else {
            $stash['by'] = $by;
            $stash['id'] = $id;
            $app->render('html/projects.html', $stash);
        }
    }
    $RIS_DBH = null;
    exit;
});

$app->get('/dataset_details/:udi', function ($udi) use ($app) {
    $GOMRI_DBH = openDB('GOMRI_RO');
    $RIS_DBH = openDB('RIS_RO');

    $SELECT = '
    SELECT
        CASE
            WHEN mv.title IS NOT NULL THEN mv.title
            WHEN r.dataset_title IS NOT NULL THEN r.dataset_title
        ELSE
            d.title
        END AS title,
        status,
        dataset_uid,
        d.dataset_udi AS udi,
        d.primary_poc AS ppoc_ris_id,
        d.secondary_poc AS spoc_ris_id,
        dataset_originator,
        CASE
            WHEN mv.abstract IS NOT NULL THEN mv.abstract
            WHEN r.dataset_abstract IS NOT NULL THEN r.dataset_abstract
            ELSE d.abstract
        END AS abstract,
        CASE WHEN r.registry_id IS NULL THEN 0 ELSE 1 END AS registered';

    $FROM = 'FROM datasets d
             LEFT OUTER JOIN registry r ON r.dataset_udi = d.dataset_udi
             LEFT OUTER JOIN metadata_view mv ON mv.registry_id  = r.registry_id';

    $WHERE = "WHERE d.dataset_udi='$udi'";

    $stmt = $GOMRI_DBH->prepare(
        "$SELECT $FROM $WHERE ORDER BY CAST(SUBSTRING(r.registry_id from 18 for 3) AS INTEGER) DESC LIMIT 1;"
    );
    $stmt->execute();
    $stash['datasets'] = $stmt->fetchAll();

    for ($i=0; $i<count($stash['datasets']); $i++) {
        $ppoc = getPeopleDetails($RIS_DBH, array('peopleId=' . $stash['datasets'][$i]['ppoc_ris_id']));
        $stash['datasets'][$i]['ppoc'] = $ppoc[0];
        if ($stash['datasets'][$i]['spoc_ris_id']) {
            $spoc = getPeopleDetails($RIS_DBH, array('peopleId=' . $stash['datasets'][$i]['spoc_ris_id']));
            $stash['datasets'][$i]['spoc'] = $spoc[0];
        }
    }

    $RIS_DBH = null;
    $GOMRI_DBH = null;
    $app->render('html/dataset_details.html', $stash);
    exit;
});

$app->get('/pdf/:by/:id/:name', function ($by, $id, $name) use ($app) {
    if ($by == 'YR1') {
        drupal_set_message(
            'You cannot download a pdf for all Year One Block Grants. Please download each block grant individually.',
            'error'
        );
        return;
    }
    $content = file_get_contents("http://localhost$_SERVER[SCRIPT_NAME]/projects/$by/$id/dompdf");

    require_once '/usr/local/share/dompdf/dompdf_config.inc.php';

    $dompdf = new DOMPDF();
    $dompdf->load_html($content);
    $dompdf->set_paper('letter', 'portrait');
    $dompdf->render();
    $dompdf->stream($name, array("Attachment" => true));

    exit;
});

$app->run();

function filterFilename($raw_filename) {
    $pattern = array('/,/', '/:/', '/\'/', '/\./', '/\//');
    $replace = array('', '', '', '_', '_');
    return preg_replace($pattern,$replace,$raw_filename);
}
