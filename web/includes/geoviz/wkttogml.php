<?php
#Debug Only
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

include_once '/usr/local/share/GRIIDC/php/pdo.php';

$wkt='';
$gml='';

if (isset($_POST["wkt"]))
{
	$wkt = $_POST["wkt"];
}

$configini = parse_ini_file("/etc/griidc/db.ini",true);
$config = $configini["GOMRI_RW"];

$dbconnstr = 'pgsql:host='. $config["host"];
$dbconnstr .= ' port=' . $config["port"];
$dbconnstr .= ' dbname=' . $config["dbname"];
$dbconnstr .= ' user=' . $config["username"];
$dbconnstr .= ' password=' . $config["password"];

$conn = pdoDBConnect($dbconnstr);

$query = "SELECT ST_asGML(3,ST_GeomFromText('$wkt',4326),5,17)";

$row = pdoDBQuery($conn,$query);

$gml = $row[0]["st_asgml"];

//echo "{\"featureid\":\"$featureid\",\"reason\":\"$reason\"}";
echo $gml;

?>

