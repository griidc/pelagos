<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

$GLOBALS['pelagos_config']  = parse_ini_file('/etc/opt/pelagos.ini',true);

require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/db-utils.lib.php';
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/rpis.php';
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/datasets.php';
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/codelists.php';

$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['Twig']['twigDirectory'];

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->get('/', function () use ($app) {
    echo <<<'EOT'
<p>Usage: /metadata-generator/$udi</p>
<p>Example: <a href="/metadata-generator/R1.x134.114:0008">/metadata-generator/R1.x134.114:0008</a></p>
EOT;
});

$app->get('/:udi', function ($udi) use ($app) {
    $stash = array();
    $GOMRI_DBH = OpenDB('GOMRI_RO');
    $RIS_DBH = OpenDB('RIS_RO');
    $datasets = get_identified_datasets($GOMRI_DBH,array("udi=$udi"));

    if (count($datasets) > 0) {
        $stash['dataset'] = $datasets[0];
        $stash['dataset']['url'] = "https://data.gulfresearchinitiative.org/data/$udi";
        if (array_key_exists('dataset_download_size',$stash['dataset'])) {
            $size_bytes = $stash['dataset']['dataset_download_size'] / 1048576;
            if ($size_bytes >= 10) $precision = 0;
            else {
                for ($precision = 1; $precision < 6; $precision++) {
                    if ($size_bytes > pow(10,-$precision)) break;
                }
            }
            $stash['dataset']['size'] = round($size_bytes,$precision);
        }
        if (array_key_exists('dataset_filename',$stash['dataset'])) {
            $stash['dataset']['formatName'] = preg_replace('/^.*\./','',$stash['dataset']['dataset_filename']);
        }

        if (array_key_exists('primary_poc',$stash['dataset'])) {
            $people = getPeopleDetails($RIS_DBH,array('peopleId='.$stash['dataset']['primary_poc']));
            if (count($people)) {
                $stash['RP']['PPOC'] = $people[0];
                $stash['RP']['PPOC']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
            }
        }
    
        if (array_key_exists('project_id',$stash['dataset'])) {
            $people = getPeopleDetails($RIS_DBH,array('projectId='.$stash['dataset']['project_id'],'RoleId=3'));
            if (count($people)) {
                $stash['RP']['DM'] = $people[0];
                $stash['RP']['DM']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['pointOfContact'];
            }
        }

        $stash['RP']['DIST'] = $GLOBALS['config']['Distributor'];
        $stash['RP']['DIST']['RoleCode'] = $GLOBALS['CodeLists']['CI_RoleCode']['distributor'];

        $stash['dataset']['metadata_filename'] = preg_replace('/:/','-',$udi) . '-metadata.xml';

        header('Content-Type: text/xml');
        if (!preg_match('/^f$|^false$|^n$|^no$/i',$app->request()->get('download'))) {
            header('Content-Disposition: attachment; filename=' . $stash['dataset']['metadata_filename']);
        }

        $app->view()->appendData($stash);
        $xml = $app->view()->render('xml/MI_Metadata.xml');

        $tidy_config = array('indent'        => true,
                             'indent-spaces' => 4,
                             'input-xml'     => true,
                             'output-xml'    => true,
                             'wrap'          => 0);

        $tidy = new tidy;
        $tidy->parseString($xml, $tidy_config, 'utf8');
        $tidy->cleanRepair();

        echo $tidy;
        exit;
    }
    else {
        drupal_set_message("UDI: $udi not found.",'error');
    }
});

$app->run();

?>
