<?php
#Debug Only
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

include_once '/usr/local/share/GRIIDC/php/pdo.php';

$wkt='';
$featureid='';

if (isset($_POST["wkt"]))
{
	$wkt = $_POST["wkt"];
	$featureid = $_POST["featureid"];
}

$configini = parse_ini_file("/etc/griidc/db.ini",true);
$config = $configini["GOMRI_RW"];

$dbconnstr = 'pgsql:host='. $config["host"];
$dbconnstr .= ' port=' . $config["port"];
$dbconnstr .= ' dbname=' . $config["dbname"];
$dbconnstr .= ' user=' . $config["username"];
$dbconnstr .= ' password=' . $config["password"];

$conn = pdoDBConnect($dbconnstr);

$query = "SELECT ST_IsValidReason(ST_GeometryFromText('$wkt'));";

$row = pdoDBQuery($conn,$query);

$reason = $row["st_isvalidreason"];

echo "{\"featureid\":\"$featureid\",\"reason\":\"$reason\"}";

?>