<?php

include_once '/home/users/mvandeneijnden/public_html/quartz/php/pdo.php';

$configini = parse_ini_file("/etc/griidc/db.ini",true);
$config = $configini["GRIIDC_RO"];

$dbconnstr = 'pgsql:host='. $config["host"];
$dbconnstr .= ' port=' . $config["port"];
$dbconnstr .= ' dbname=' . $config["dbname"];
$dbconnstr .= ' user=' . $config["username"];
$dbconnstr .= ' password=' . $config["password"];

$dbconn = pdoDBConnect($dbconnstr);

$query = 'SELECT DISTINCT "Person_Number" AS "id", "Person_LastName" || \', \' || "Person_FirstName" AS "label", "Person_LastName" || \', \' || "Person_FirstName" AS "value" FROM "Person" WHERE "Person_Number" <> 0';

if (isset($_GET["term"]))
{
    $searchTerm = $_GET["term"];
    $query .= ' AND "Person_LastName" ilike \''.$searchTerm.'%\' ';
}

$query .= 'ORDER BY "label"';

//echo $query;

$rows = pdoDBQuery($dbconn,$query);

foreach ($rows as $row)
{
    $people[] = array('id'=>$row['id'],'label'=>$row['label'],'value'=>$row['value']);
}

// echo '<pre>';
// var_dump($people);
// echo '</pre>';

header('Content-Type: application/json');

echo json_encode($people);


?>