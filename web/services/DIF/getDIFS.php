<?php

header('Content-Type: application/json');

$personID = null;

if (isset($_GET["person"]) AND $_GET["person"] != '')
{
    $personID = $_GET["person"];
}


define('RPIS_TASK_BASEURL','http://localhost/services/RIS/getTaskDetails.php');
//define('RPIS_TASK_BASEURL','http://proteus.tamucc.edu/services/RPIS/getTaskDetails.php');
//define('RPIS_TASK_BASEURL','http://data.gulfresearchinitiative.org/services/RPIS/getTaskDetails.php');

$pelagos_config  = parse_ini_file('/etc/opt/pelagos.ini',true);
include_once $pelagos_config['paths']['share'].'/php/pdo.php';

$configini = parse_ini_file($pelagos_config['paths']['conf'].'/db.ini',true);
$config = $configini["GOMRI_RW"];

$dbconnstr = 'pgsql:host='. $config["host"];
$dbconnstr .= ' port=' . $config["port"];
$dbconnstr .= ' dbname=' . $config["dbname"];
$dbconnstr .= ' user=' . $config["username"];
$dbconnstr .= ' password=' . $config["password"];

$dbconn = pdoDBConnect($dbconnstr);

// $switch = '?'.'maxResults=-1&cached=true';

// if (isset($_GET["person"]) AND $_GET["person"] != '')
// {
    // $searchTerm = $_GET["person"];
    // $switch = "?peopleID=$searchTerm&maxResults=-1";
// }

// $tasks = array();

// $doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch);

$tasks = getTasks($personID,true);

# if we have no task roles, try to get tasks for which we have any role

//var_dump($tasks);

$onlyProjects = true;

foreach ($tasks as $task) { if ((int)$task['ID'] != 0) { $onlyProjects = false;} }

if (count($tasks) == 0 OR $onlyProjects) {
    $tasks = getTasks($personID,false);
}

$stuff = displayTaskStatus($tasks,$dbconn);

sort($stuff);

echo json_encode($stuff);

exit;

function displayTaskStatus($tasks,$conn,$update=null,$personid=null,$filterstatus=null)
{
    $ShowEmpty = true;
    
    if (isset($_GET["showempty"]))
    {
        $ShowEmpty = $_GET["showempty"];
    }
    
    $resArray = array();
    
    $projectID ="";
    $taskTitle="";
    $taskID ="";
    foreach ($tasks as $task)
    {
        $projectArr = array();
        $childArr = array();
        $taskID = (string)$task['ID'];
        $taskTitle = (string)$task->Title;
        $projectID = (string)$task->Project['ID'];
        
        //echo "d.add($nodeCount,0,'".addslashes($taskTitle)."','javascript: d.o($nodeCount);','".addslashes($taskTitle)."','','','',true);\n";
        
        if ($taskID > 0)
        {
            $query = "select title,status,dataset_uid,dataset_udi from datasets where task_uid=$taskID";
        }
        else
        {
            $query = "select title,status,dataset_uid,dataset_udi from datasets where project_id=$projectID";
            
        }       
        
        if (isset($_GET["status"]) AND $_GET["status"] != '')
        {
            $statusFlag = $_GET["status"];
            $query .= " AND status=$statusFlag";
        }
        
        $query .= " ORDER BY title;";
        
        $rows = pdoDBQuery($conn,$query);
        
        if ($rows != null)
        {
            
            
            foreach ($rows as $row) 
            {
                $status = (integer)$row["status"];
                $title = htmlspecialchars($row["title"]);
                $datasetid = $row["dataset_uid"];
                $dataset_udi = $row["dataset_udi"];
                
                $qs = "uid=$datasetid";
                if (isset($personid)) { $qs .= "&prsid=$personid"; }
                if (array_key_exists('as_user',$_GET)) { $qs .= "&as_user=$_GET[as_user]"; }
                
                if ($filterstatus==$status OR $filterstatus==null OR $filterstatus=="")
                {
                    
                    //echo "d.add($nodeCount,$folderCount,'".addslashes("[$dataset_udi] $title")."','?$qs','".addslashes("[$dataset_udi] $title")."','_self'";
                    
                    switch ($status)
                    {
                        case null:
                        $icon =  '/images/icons/cross.png';
                        break;
                        case 0:
                        $icon = '/images/icons/cross.png';
                        break;
                        case 1:
                        $icon = '/images/icons/error.png';
                        break;
                        case 2:
                        $icon =  '/images/icons/tick.png';
                        break;
                    }
                }
                
                $childArr[] = array("text"=>"[$dataset_udi] $title","icon"=>$icon,"li_attr"=>array("longtitle"=>$title),"a_attr"=>array("onclick"=>"getNode('$dataset_udi');"));
                
            }
            $resArray[] = array("text"=>$taskTitle,"icon"=>"/images/icons/folder.png","state"=>array("opened"=>true),"children"=>$childArr,"li_attr"=>array("longtitle"=>$title),"a_attr"=>array("style"=>"color:black;cursor:default;opacity:1;background-color:transparent;box-shadow:none"));  
        }
        else
        {
            if ($ShowEmpty)
            {         
                $resArray[] = array("text"=>$taskTitle,"icon"=>"/images/icons/folder_gray.png","state"=>array("opened"=>false,"disabled"=>true),"children"=>$childArr,"li_attr"=>array("longtitle"=>$taskTitle,"style"=>"color:black"),"a_attr"=>array("style"=>"color:gray;cursor:default;opacity:.7;"));       
            }
        }
    }
    
    return $resArray;
}

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
