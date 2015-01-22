<?php
/**********************
 * Journal DATA LAYER *
 **********************/

function insertJournal($parameters)
{
    include_once '/home/users/mvandeneijnden/pelagos/share/php/DBConnection.php'; 
    $DBConn = new DBConnection('GOMRI_RO');
    
    if (count($parameters) == 4) {        
        $query = 'SELECT udf_insert_journal(?, ?, ?, ?);';
    } else {
        $query = 'SELECT udf_insert_journal(?, ?, ?);';
    }
    return $DBConn->executeQuery($query,$parameters);
}

function getJournalList()
{
    include_once '/usr/local/share/GRIIDC/php/db-utils.lib.php'; 
    $connection = OpenDB('GOMRI_RO');
    
    $query = 'SELECT journal_name, journal_issn FROM udf_get_journals;';
    
    $statementHandler = $connection->prepare($query);
    $rc = $statementHandler->execute($parameters);
    if (!$rc) {return $statementHandler->errorInfo();};
    return $statementHandler->fetchAll();
}

function getJournal($parameters)
{
    include_once '/home/users/mvandeneijnden/pelagos/share/php/DBConnection.php'; 
    $DBConn = new DBConnection('GOMRI_RO');
    
    $query = 'select "hello","test";';
    
    return $DBConn->executeQuery($query);
}
 
function deleteJournal($connection, $difID)
{
    include_once '/usr/local/share/GRIIDC/php/db-utils.lib.php'; 
    $conn = OpenDB('GOMRI_RO');
    
    $query = "select *, st_AsGML(geom) as \"the_geom\" from datasets where dataset_udi='$difID';";
    
    $statementHandler = $connection->prepare($query);
    $rc = $statementHandler->execute($parameters);
    if (!$rc) {return $statementHandler->errorInfo();};
    return $statementHandler->fetchAll();
}
?>