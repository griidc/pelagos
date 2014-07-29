<?php

header('Content-Type: application/json');

define('RPIS_TASK_BASEURL','http://localhost/services/RIS/getTaskDetails.php');

include_once '/usr/local/share/GRIIDC/php/pdo.php';

$configini = parse_ini_file("/etc/griidc/db.ini",true);
$config = $configini["GOMRI_RW"];

$dbconnstr = 'pgsql:host='. $config["host"];
$dbconnstr .= ' port=' . $config["port"];
$dbconnstr .= ' dbname=' . $config["dbname"];
$dbconnstr .= ' user=' . $config["username"];
$dbconnstr .= ' password=' . $config["password"];

$dbconn = pdoDBConnect($dbconnstr);

$switch = '?'.'maxResults=-1&cached=true';

if (isset($_GET["person"]) AND $_GET["person"] != '')
{
    $searchTerm = $_GET["person"];
    $switch = "?peopleID=$searchTerm&maxResults=-1";
}


$tasks = array();

$doc = simplexml_load_file(RPIS_TASK_BASEURL.$switch);
$rpisTasks = $doc->xpath('Task');

$stuff = displayTaskStatus($rpisTasks,$dbconn);

//echo '<pre>';
//var_dump($stuff);
//echo '</pre>';


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
        
        $query .= " ORDER BY dataset_udi";
        
        $rows = pdoDBQuery($conn,$query);
        
        //var_dump($rows);
        
        //exit;
        
        if ($rows != null)
        {
            
            
            foreach ($rows as $row) 
            {
                
                //var_dump($row);
                
                //exit;
                $status = (integer)$row["status"];
                $title = $row["title"];
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



echo json_encode($stuff);


?>