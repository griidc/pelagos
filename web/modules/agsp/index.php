<?php

error_reporting(E_ALL);
ini_set( 'display_errors','1');

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once '/usr/local/share/GRIIDC/php/pdo.php';
require_once '/usr/local/share/GRIIDC/php/datasets.php';
require_once '/usr/local/share/GRIIDC/php/rpis.php';
require_once '/usr/local/share/GRIIDC/php/utils.php';

require_once 'lib/agsp.php';

$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['TwigView']['twigDirectory'];

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->get('/', function () use ($app) {
});

$app->get('/datasets', function () use ($app) {

    $query = "SELECT COUNT(downloads.registry_id) as total_number_of_downloads,
                     (SELECT SUM(dataset_download_size) FROM registry) AS total_file_size,
                     (SELECT COALESCE(SUM(dataset_download_size),0) FROM registry WHERE substr(registry.registry_id,0,17) = dataset_udi AND dataset_download_status = 'done') AS total_file_size_by_gomri
                     FROM registry
                     LEFT OUTER JOIN datasets on registry.dataset_udi = datasets.dataset_udi
                     LEFT OUTER JOIN downloads on downloads.registry_id = substr(registry.registry_id,0,17);";

    $fsrow = pdoDBQuery(getDBH('GOMRI'),$query);
    $stash['avail_storage_space'] = bytes2filesize($GLOBALS['config']['misc']['system_capacity'] - $fsrow["total_file_size"],1);
    $stash['used_storage_space'] = bytes2filesize($fsrow["total_file_size"],1);

    $fundFilter = array('fundId>0');
    if (isset($GLOBALS['config']['exclude']['funds'])) {
    	foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
    		$fundFilter[] = "fundId!=$exclude";
    	}
    }

    $FUNDS = getFundingSources(getDBH('RPIS'),$fundFilter);

    $resultArr = array();
    $resultSet = 0;

    $stash['identified_total'] = 0;
    $stash['registered_total'] = 0;

    foreach ($FUNDS as $FUND) {
        $identified_count = count_identified_datasets(getDBH('GOMRI'),array('status>0',"funding_envelope=$FUND[ID]"));
    	$registered_count = count_registered_datasets(getDBH('GOMRI'),array("funding_envelope=$FUND[ID]"));
        $resultArr[$resultSet] = $FUND;
    	$resultArr[$resultSet]['identified_count'] = $identified_count;
        $stash['identified_total'] += $identified_count;
    	$resultArr[$resultSet]['registered_count'] = $registered_count;
        $stash['registered_total'] += $registered_count;
    	$resultSet++;
    }

    usort($resultArr, 'cmp_registered_identified');

    $stash['funds'] = $resultArr;

    $app->render('html/datasets.html',$stash);

    exit;
});

$app->get('/datasets/by_fund/:fund_id', function ($fund_id) use ($app) {
    $funds = getFundingSources(getDBH('RPIS'),array("fundId=$fund_id"));
    $stash['fund'] = $funds[0];

    $projects = getProjectDetails(getDBH('RPIS'),array("fundsrc=$fund_id"));

    for ($i=0; $i<count($projects); $i++) {
        $pi = getPeopleDetails(getDBH('RPIS'),array('projectId='.$projects[$i]['ID'],'roleId=1'));
        $projects[$i]['PI'] = $pi[0];
        $projects[$i]['identified_count'] = count_identified_datasets(getDBH('GOMRI'),array('status>0','projectid='.$projects[$i]['ID']));
        $projects[$i]['registered_count'] = count_registered_datasets(getDBH('GOMRI'),array('projectid='.$projects[$i]['ID']));
        $projects[$i]['tree_node_id'] = "$fund_id/".$projects[$i]['ID'];
    }
    usort($projects, 'cmp_registered_identified');
    $stash['projects'] = $projects;

    $app->render('html/funding.html',$stash);
    exit;
});

$app->run();

?>
