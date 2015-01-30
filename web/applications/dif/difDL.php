<?php
/**************
 * DATA LAYER *
 **************/
 
include_once '../../../share/php/db-utils.lib.php'; 

//global $myDC;

//$myDC = new DataConnector('GOMRI_RO');

function saveDIF($parameters)
{
    //global $myDC;
    //$conn = $myDC->connection;
    
    $conn = OpenDB('GOMRI_RO');

/*    
    dataset_uid_i integer,    -dataset_udi_t text,    -project_id_i integer,    -task_uid_i integer,    -title_t text,    -primary_poc_i integer,    -secondary_poc_i integer,    -abstract_t text,    -dataset_type_t text,    -dataset_for_t text,    -size_t text,    -observation_t text,    approach_t text,    start_date_d date,    end_date_d date,    geo_location_t text,    point_t text,    national_t text,    ethical_t text,    remarks_t text,
    logname_i integer,    status_i integer,    editor_t text,    geom_gml text,    funding_source text,    submitted_t text
*/    
    $query = 'select save_dif(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    
    $statementHandler = $conn->prepare($query);
    //$myDC->prepare($query);
    //$rc = $myDC->execute($parameters);
    $rc = $statementHandler->execute($parameters);
    if (!$rc) {return $statementHandler->errorInfo();};
    //return $myDC->fetchAll();
    return $statementHandler->fetchAll();
}
 
function loadDIFData($difID)
{
    //global $myDC;
    //$conn = makeConn("GOMRI_RO");
    //$conn = makeConn("GRIIDC_RO");
    //$conn = $myDC->connection;
    $conn = OpenDB('GOMRI_RO');
    
    $query = "select *, st_AsGML(geom) as \"the_geom\" from datasets where dataset_udi='$difID';";
    
    //$query = 'select * from "DataGroup_view" where "UDI"=\''.$difID.'\'';
    
    //echo $query;
    
    // $myDC->prepare($query);
    // $rc = $myDC->execute();
    // if (!$rc) {return $false;};
    // return $myDC->fetchAll();  
    
    $statementHandler = $conn->prepare($query);
    $rc = $statementHandler->execute($parameters);
    if (!$rc) {return $statementHandler->errorInfo();};
    return $statementHandler->fetchAll();
}

function loadResearchers($PseudoID=null,$PersonID=null)
{
    global $myDC;
    //$conn = makeConn("GOMRI_RO");
    //$conn = makeConn("GRIIDC_RO");
    //$conn = $myDC->connection;
    //$query = "select * from datasets where dataset_uid='$difID';";
    
    $query = 'select * from "PersonTask_view"';
    
    if (isset($PseudoID))
    {
        $query .= ' where "PseudoTask_Number" ='.$PseudoID.' ORDER BY "Person_Name";';
    }
    
    if (isset($PersonID))
    {
        $query .= ' where "Person_Number" ='.$PersonID.';';
    }
    
    //echo $query;
    
    $myDC->prepare($query);
    
    return $myDC->execute();   
}

function loadTaskData($PseudoID=null,$Status=null)
{
    global $myDC;
    //$conn = makeConn("GRIIDC_RO");
    //$conn = $myDC->connection;
    $query = 'select * from "DataGroupTasks_view" WHERE 1=1';
    
    // if (isset($UDI) AND $UDI !='')
    // {
        // $query .= ' AND "UDI"='.$UDI;
    // }
    
    if (isset($PseudoID))
    {
        $query .= ' AND "PseudoTask_Number"='.$PseudoID;
    }
    
    if (isset($Status) AND $Status !='')
    {
        $query .= ' AND "Access_Status"=\''.$Status.'\'';
    }
    
    //echo $query;
    
    $myDC->prepare($query);
    //$result = pdoDBQuery($conn,$query);  
    
    return $myDC->execute(); 
}

function loadTasks($Person)
{
    global $myDC;
    
    $parameters = array();

    $query = 'SELECT DISTINCT "PseudoTask_Number", "Task_Title" FROM "DataGroupTasks_view" WHERE 1=1';
    
    if (isset($Person) AND $Person !='')
    {
        $query .= ' AND "PseudoTask_Number" IN (SELECT "PseudoTask_Number" from "PersonTask_view" WHERE "Person_Number"=:personid) ';
        $parameters = array('personid'=>$Person);
    }
    
    $query .= ' ORDER BY "Task_Title";';
    
    $myDC->prepare($query);
        
    return $myDC->execute($parameters);  
}

function displayTaskStatus($tasks,$conn,$update=null,$personid=null,$filterstatus=null)
{
    $conn = OpenDB('GOMRI_RO');
    
    $ShowEmpty = true;
    
    if (isset($_POST["showempty"]))
    {
        $ShowEmpty = $_POST["showempty"];
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
        
        if (isset($_POST["status"]) AND $_POST["status"] != '')
        {
            $statusFlag = $_POST["status"];
            $query .= " AND status=$statusFlag";
        }
        
        $query .= " ORDER BY dataset_udi,title;";
        
        $statementHandler = $conn->prepare($query);
        $rc = $statementHandler->execute();
        if (!$rc) {return $statementHandler->errorInfo();};
        $rows = $statementHandler->fetchAll();
        
        if ($rows != null)
        {
            
            
            foreach ($rows as $row) 
            {
                $status = (integer)$row["status"];
                $title = htmlspecialchars($row["title"]);
                $datasetid = $row["dataset_uid"];
                $dataset_udi = $row["dataset_udi"];
                
                // $qs = "uid=$datasetid";
                // if (isset($personid)) { $qs .= "&prsid=$personid"; }
                // if (array_key_exists('as_user',$_GET)) { $qs .= "&as_user=$_GET[as_user]"; }
                
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
