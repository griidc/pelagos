<?php
/**************
 * DATA LAYER *
 **************/
 
include_once '/home/users/mvandeneijnden/public_html/quartz/php/db-utils.lib.php'; 

global $myDC;

$myDC = new DataConnector('GOMRI_RO');

function saveDIF($parameters)
{
    global $myDC;
    $conn = $myDC->connection;

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
    
    $myDC->prepare($query);
    
    $rc = $myDC->execute($parameters);
    
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