<?php
// @codingStandardsIgnoreFile

error_reporting(E_ALL & ~E_NOTICE);

header('Content-Type: application/json');

define('RPIS_TASK_BASEURL','http://localhost/services/RIS/getTaskDetails.php');

$switch = '';

if (isset($_GET["pseudoid"]))
{
    $pseudoID = $_GET["pseudoid"];

    $projectID = intval($pseudoID/1024);

    $switch = '?projectID='.$projectID;
}
else
{echo '{}';exit;}



$doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch);
$rpisTasks = $doc->xpath('Task');


$w = 's';

$bb=array();

$primaryID = 0;

//var_dump($rpisTasks);

$peops = $rpisTasks[0]->xpath('Researchers/Person');
foreach ($peops as $peoples) {
    $personID = (int)$peoples['ID'];
    $roles = $peoples->Roles;


    //exit;

    foreach ($roles as $role)
    {
        $roleName = $role->Role->Name;

        if ($roleName == 'Principal Investigator')
        {
           $isPrimary = true;
        }
        else
        {
            $isPrimary = false;
        }
    }

    $LastName = preg_replace('/\'/','\\\'',$peoples->LastName);
    $FirstName = preg_replace('/\'/','\\\'',$peoples->FirstName);
    $Email = preg_replace('/\'/','\\\'',$peoples->Email);
    if (!$Email){}else{$Email = " ($Email)";}
    $line = array('ID'=>$personID,'Contact'=>$LastName.', '.$FirstName.$Email,'isPrimary'=>$isPrimary);

    array_push($bb,$line);
}
array_unique($bb);
sort($bb);
echo json_encode($bb);

