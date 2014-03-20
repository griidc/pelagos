<?php
#Debug Only
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

include_once '/usr/local/share/GRIIDC/php/pdo.php';

$wkt='';
$gml='';

if (isset($_POST["gml"]))
{
	$gml = $_POST["gml"];
}

$configini = parse_ini_file("/etc/griidc/db.ini",true);
$config = $configini["GOMRI_RW"];

$dbconnstr = 'pgsql:host='. $config["host"];
$dbconnstr .= ' port=' . $config["port"];
$dbconnstr .= ' dbname=' . $config["dbname"];
$dbconnstr .= ' user=' . $config["username"];
$dbconnstr .= ' password=' . $config["password"];

$conn = pdoDBConnect($dbconnstr);

$query = "SELECT ST_asText(ST_GeomFromGML('$gml', 4326));";

$row = pdoDBQuery($conn,$query);

$wkt = $row["st_astext"];

//echo "{\"featureid\":\"$featureid\",\"reason\":\"$reason\"}";
echo $wkt;

?>