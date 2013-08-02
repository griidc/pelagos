<?php

require_once '/usr/local/share/GRIIDC/php/rpis.php';
require_once '/usr/local/share/GRIIDC/php/datasets.php';

$GLOBALS['config'] = parse_ini_file('config.ini',true);

$RPIS_DBH = getDBH('RPIS');
$GOMRI_DBH = getDBH('GOMRI');

$fundFilter = array('fundId>0');
if (isset($GLOBALS['config']['exclude']['funds'])) {
	foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
		$fundFilter[] = "fundId!=$exclude";
	}
}

$FUNDS = getFundingSources($RPIS_DBH,$fundFilter);

$resultArr = array();
$resultSet = 0;

foreach ($FUNDS as $FUND) {
	$identified_count = 0;
	$registered_count = 0;
	$projectFilter = array("fundSrc=$FUND[ID]");
	$projects = getProjectDetails($RPIS_DBH,$projectFilter);
	foreach ($projects as $project) {
		$identified_count += count_identified_datasets($GOMRI_DBH,array("projectId=$project[ID]"));
		$registered_count += count_registered_datasets($GOMRI_DBH,array("projectId=$project[ID]"));
	}
	$resultArr[$resultSet]["identified"] = $identified_count;
	$resultArr[$resultSet]["registered"] = $registered_count;
	$resultArr[$resultSet]["fundingsource"] = $FUND["Abbr"];
	$resultSet ++;
}

usort($resultArr, "cmp");

foreach ($resultArr as $result)
{
	print "<tr><td>$result[identified]</td><td>$result[registered]</td><td><button type=\"submit\" name=\"fundsrc\" value=\"$result[fundingsource]\">$result[fundingsource]</button></td></tr>";
}
//echo '<pre>';
//var_dump($resultArr);
//echo '</pre>';

function cmp($a, $b)
{
    if ($a['identified'] == $b['identified']) {
        return 0;
    }
    return ($a['identified'] > $b['identified']) ? -1 : 1;
}

function getDBH($db) {
    $dbh = new PDO($GLOBALS['config'][$db.'_DB']['connstr'],
                   $GLOBALS['config'][$db.'_DB']['username'],
                   $GLOBALS['config'][$db.'_DB']['password'],
                   array(PDO::ATTR_PERSISTENT => true));

    if ($db == 'RPIS') {
        $stmt = $dbh->prepare('SET character_set_client = utf8;');
        $stmt->execute();
        $stmt = $dbh->prepare('SET character_set_results = utf8;');
        $stmt->execute();
    }

    return $dbh;
}

?>
