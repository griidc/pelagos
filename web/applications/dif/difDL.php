<?php
/**************
 * DATA LAYER *
 **************/
 
include_once '/home/users/mvandeneijnden/public_html/quartz/php/db-utils.lib.php'; 

global $myDC;

$myDC = new DataConnector('GOMRI_RO');

function saveDIF($data)
{
    global $myDC;
    $conn = $myDC->connection;
    
    $UDI = $data["udi"];
    $status = (int)$data["status"];
    $title = $data["title"];
    $primarypoc = (int)$data["primarypoc"];
    $secondarypoc = (int)$data["secondarypoc"];
    $abstract = $data["abstract"];
    $pseudoID = (int)($data["task"]);
    $projectID = intval($pseudoID/1024);
    $taskID = $pseudoID - ($projectID*1024);
    $datasettype = $data["dtascii"].'|'.$data["dtuascii"].'|'.$data["dtimages"].'|'.$data["dtnetcdf"].'|'.$data["dtvideo"].'|'.$data["dtgml"].'|'.$data["dtvideoatt"].'|'.$data["dtother"];
    $datasetfor = $data["dfeco"].'|'.$data["dfphys"].'|'.$data["dfatm"].'|'.$data["dfchem"].'|'.$data["dfhumn"].'|'.$data["dfscpe"].'|'.$data["dfeconom"].'|'.$data["dfother"];
    $datasize = $data["size"];
    $approach = $data["appField"].'|'.$data["appSim"].'|'.$data["appLab"].'|'.$data["appLit"].'|'.$data["appRemote"].'|'.$data["appOther"];
    $observation = $data["observation"];
    $startdate = $data["startdate"];
    $enddate = $data["enddate"];
    $geolocation = $data["spatialdesc"];
    $submission = $data["accFtp"].'|'.$data["accTds"].'|'.$data["accErdap"].'|'.$data["accOther"];
    $natarchive = $data["repoNodc"].'|'.$data["repoUsepa"].'|'.$data["repoGbif"].'|'.$data["repoNcbi"].'|'.$data["repoDatagov"].'|'.$data["repoGriidc"].'|'.$data["repoOther"];
    $ethical = $data["privacy"].'|'.$data["privacyother"];
    $remarks = $data["remarks"];
    $datasetUID = time();
    $fundingSource = 'T1';
    $gmlText = $data["geoloc"];
    
    if (empty($startdate)){$startdate=null;};
    
    if (empty($enddate)){$enddate=null;};
    
    if (empty($gmlText)){$gmlText=null;};
    
    //$gmlText = '<gml:Point srsName="urn:ogc:def:crs:EPSG::4326"><gml:pos srsDimension="2">27.70323 -97.30042</gml:pos></gml:Point>';
    $editor = getUID();
    
    $logname = getPersonID(getUID());
    
    
    
    // echo "taskid";
    // var_dump($taskID);
    // echo "projectID";
    // var_dump($projectID);
    // echo "pseudoid";
    // var_dump($pseudoid);
    
/*    
    -dataset_uid_i integer,
    -dataset_udi_t text,
    -project_id_i integer,
    -task_uid_i integer,
    -title_t text,
    -primary_poc_i integer,
    -secondary_poc_i integer,
    -abstract_t text,
    -dataset_type_t text,
    -dataset_for_t text,
    -size_t text,
    -observation_t text,
    -approach_t text,
    -start_date_d date,
    -end_date_d date,
    -geo_location_t text,
    -point_t text,
    -national_t text,
    ethical_t text,
    remarks_t text,
    logname_i integer,
    status_i integer,
    editor_t text,
    geom_gml text,
    funding_source text
*/    
    $query = 'select save_dif(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    
    //$query  = 'select 1=1;';
    
    $myDC->prepare($query);
    
    $parameters = array($datasetUID,$UDI,$projectID,$taskID,$title,$primarypoc,$secondarypoc,$abstract,$datasettype,$datasetfor,$datasize,$observation,$approach,$startdate,$enddate,$geolocation,$submission,$natarchive,$ethical,$remarks,$logname,$status,$editor,$gmlText,$fundingSource);
    
    $rc = $myDC->execute($parameters);
    
    //var_dump($rc);
    
    return $rc;
    
}
 
function loadDIFData($difID)
{
    global $myDC;
    //$conn = makeConn("GOMRI_RO");
    //$conn = makeConn("GRIIDC_RO");
    $conn = $myDC->connection;
    $query = "select *, st_AsGML(geom) as \"the_geom\" from datasets where dataset_uid='$difID';";
    
    //$query = 'select * from "DataGroup_view" where "UDI"=\''.$difID.'\'';
    
    //echo $query;
    
    $myDC->prepare($query);
    
    return $myDC->execute();   
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

?>