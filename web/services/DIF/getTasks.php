<?php
define('RPIS_TASK_BASEURL','http://proteus.tamucc.edu/services/RPIS/getTaskDetails.php');

$switch = '?'.'cached=true&maxResults=-1';

$tasks = array();

if (isset($_GET["person"]) AND $_GET["person"] != '')
{
    $personID= $_GET['person'];
    $switch = '?'.'peopleID='.$personID.'&maxResults=-1';;
}

$doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch);
$rpisTasks = $doc->xpath('Task');

//var_dump($switch);

foreach ($rpisTasks as $task)
{

    $maxLength = 200;
    if (strlen($task->Title) > $maxLength){
        $taskTitle=substr((string)$task->Title,0,$maxLength).'...';
    } else {
        $taskTitle=(string)$task->Title;
    }
        
    $fundingSource = (string)$task->Project->FundingSource["ID"];
    
    $taskID = (string)$task["ID"];
    $projectID = (string)$task->Project["ID"];
    $pseudoID = ((int)$projectID * 1024)+ (int)$taskID;
    $tasks[] = array('Title'=>$taskTitle,'ID'=>$pseudoID,'taskID'=>$taskID,'projectID'=>$projectID,'fundSrcID'=>$fundingSource);
    
}    

sort($tasks);

header('Content-Type: application/json');

echo json_encode($tasks);

?>