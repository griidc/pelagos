<?php
define('RPIS_TASK_BASEURL','http://localhost/services/RIS/getTaskDetails.php');

// $switch = '?'.'cached=true&maxResults=-1';

// $tasks = array();

// if (isset($_GET["person"]) AND $_GET["person"] != '')
// {
    // $personID= $_GET['person'];
    // $switch = '?'.'peopleID='.$personID.'&maxResults=-1';;
// }

// $doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch);
// $rpisTasks = $doc->xpath('Task');

//var_dump($switch);

$personID = null;

if (isset($_GET["person"]) AND $_GET["person"] != '')
{
    $personID = $_GET["person"];
}

$rpisTasks = getTasks($personID,true);

# if we have no task roles, try to get tasks for which we have any role
if (count($rpisTasks) == 0) {
    $rpisTasks = getTasks($personID,false);
}

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

function getTasks($peopleid,$restrict_to_task_roles=false) 
{
    $switch = '?'.'maxResults=-1';
    
    $tasks = array();
    
    $filters = '';
    
    # get all tasks based on reseacher RIS ID
    # only search by peopleid if we have one
    if (isset($peopleid))
    {
        $filters = "&peopleid=$peopleid";
    }
    else
    {
        $filters = "&cached=true";
    }
    $doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch.$filters);
    $my_tasks = $doc->xpath('Task');
    
    foreach ($my_tasks as $task)
    {
        # for projects with no tasks just add the ficticious project task
        if ($task['ID'] == 0)
        {
            $tasks[] = $task;
            continue;
        }
        
        $currentPerson = null;
        $people = $task->xpath('Researchers/Person');
        foreach ($people as $person)
        {
            $personArray = (array)$person;
            
            if ($personArray['@attributes']['ID'] == $peopleid)
            {
                $currentPerson = $person;
                break;
            }
        }
        
        # if we want to restrict the task list to tasks for which we have a task role
            if ($restrict_to_task_roles)
            {
                if ($currentPerson)
                {
                    $roles = $currentPerson->xpath('Roles/Role/Name');
                    $taskLead = false;
                    foreach ($roles as $role)
                    {
                        if (in_array($role['ID'],array(4,5,6)))
                        {
                            $taskLead = true;
                        }
                    }
                    if ($taskLead) {
                        $tasks[] = $task;
                    }
                }
            }
            # otherwise just return all the tasks for which we have any role
            else
            {
                $tasks[] = $task;
            }
        }
        
        return $tasks;
    }
    

?>