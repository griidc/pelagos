<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'Augmenting GoMRI Summary Page';

// Load global config.
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

// Check for local config file.
if (file_exists('config.ini')) {
    // Load Common library from global share.
    require_once($GLOBALS['config']['paths']['share'].'/php/Common.php');
    // Merge local config with global config.
    $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
}

// Add Pelagos share/php to include path.
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']['paths']['share'] . '/php');

// Load Pelagos libraries.
require_once 'DBUtils.php';
require_once 'datasets.php';
require_once 'RIS.php';
require_once 'utils.php';

// Load AGSP application library.
require_once 'lib/agsp.php';

// Create new Slim application.
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

// Stub root.
$app->get(
    '/',
    function () use ($app) {
    }
);

// Respond to request for datasets summary.
$app->get(
    '/datasets',
    function () use ($app) {

        $query = "SELECT COUNT(downloads.registry_id) as total_number_of_downloads,
                         (SELECT SUM(dataset_download_size) FROM registry) AS total_file_size,
                         (SELECT COALESCE(SUM(dataset_download_size),0) FROM registry
                         WHERE substr(registry.registry_id,0,17) = dataset_udi AND
                         dataset_download_status = 'Completed') AS total_file_size_by_gomri
                         FROM registry
                         LEFT OUTER JOIN datasets on registry.dataset_udi = datasets.dataset_udi
                         LEFT OUTER JOIN downloads on downloads.registry_id = substr(registry.registry_id,0,17);";

        $GOMRI_DBH = openDB('GOMRI_RO');
        $stmt = $GOMRI_DBH->prepare($query);
        $stmt->execute();
        $fsrow = $stmt->fetchAll();
        $fsrow = $fsrow[0];
        $stash['avail_storage_space'] = bytes2filesize(
            $GLOBALS['config']['misc']['system_capacity'] - $fsrow["total_file_size"],
            1
        );
        $stash['used_storage_space'] = bytes2filesize($fsrow["total_file_size"], 1);

        $fundFilter = array('fundId>0');
        if (isset($GLOBALS['config']['exclude']['funds'])) {
            foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                $fundFilter[] = "fundId!=$exclude";
            }
        }

        $RIS_DBH = openDB('RIS_RO');

        $FUNDS = getFundingSources($RIS_DBH, $fundFilter);

        $resultArr = array();
        $resultSet = 0;

        $stash['identified_total'] = 0;
        $stash['registered_total'] = 0;

        foreach ($FUNDS as $FUND) {
            $identified_count = count_identified_datasets($GOMRI_DBH, array('status>0',"funding_envelope=$FUND[ID]"));
            $registered_count = count_registered_datasets($GOMRI_DBH, array("funding_envelope=$FUND[ID]"));
            $resultArr[$resultSet] = $FUND;
            $resultArr[$resultSet]['identified_count'] = $identified_count;
            $stash['identified_total'] += $identified_count;
            $resultArr[$resultSet]['registered_count'] = $registered_count;
            $stash['registered_total'] += $registered_count;
            $resultSet++;
        }

        usort($resultArr, 'cmpRegisteredIdentified');

        $stash['funds'] = $resultArr;

        $app->render('html/datasets.html', $stash);

        exit;
    }
);

// Respond to requests for dataset summary by fund ID.
$app->get(
    '/datasets/by_fund/:fund_id',
    function ($fund_id) use ($app) {
        $RIS_DBH = openDB('RIS_RO');
        $funds = getFundingSources($RIS_DBH, array("fundId=$fund_id"));
        $stash['fund'] = $funds[0];

        $projects = getProjectDetails($RIS_DBH, array("fundsrc=$fund_id"));

        $GOMRI_DBH = openDB('GOMRI_RO');

        for ($i=0; $i<count($projects); $i++) {
            $pi = getPeopleDetails($RIS_DBH, array('projectId='.$projects[$i]['ID'],'roleId=1'));
            $projects[$i]['PI'] = $pi[0];
            $projects[$i]['identified_count'] = count_identified_datasets(
                $GOMRI_DBH,
                array('status>0','projectid='.$projects[$i]['ID'])
            );
            $projects[$i]['registered_count'] = count_registered_datasets(
                $GOMRI_DBH,
                array('projectid='.$projects[$i]['ID'])
            );
            $projects[$i]['tree_node_id'] = $funds[0]['Abbr'] .  '/' .
                (($fund_id == 7 and array_key_exists('Abbr', $projects[$i])) ?
                    $projects[$i]['Abbr'] : $projects[$i]['ID']);
        }
        usort($projects, 'cmpRegisteredIdentified');
        $stash['projects'] = $projects;

        $app->render('html/funding.html', $stash);
        exit;
    }
);

// Run the Slim application.
$app->run();
