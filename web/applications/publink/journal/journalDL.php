<?php
/**********************
 * Journal DATA LAYER *
 **********************/

function insertJournal($parameters)
{
    include_once '../../../../share/php/DBUtils.php';
    $connection = OpenDB('GOMRI_RO');
    
    if (count($parameters) == 4) {
        $query = 'SELECT udf_insert_journal(?, ?, ?, ?);';
    } else {
        $query = 'SELECT udf_insert_journal(?, ?, ?);';
    }
    $statementHandler = $connection->prepare($query);
    $rc = $statementHandler->execute();
    if (!$rc) {
        return $statementHandler->errorInfo();
    }
    return $statementHandler->fetchAll();
}

function getJournalList()
{
    include_once '../../../../share/php/DBUtils.php';
    $connection = OpenDB('GOMRI_RO');
    
    $query = 'SELECT journal_name, journal_issn FROM udf_get_journals();';
    
    $statementHandler = $connection->prepare($query);
    $rc = $statementHandler->execute();
    if (!$rc) {
        return $statementHandler->errorInfo();
    };
    return $statementHandler->fetchAll();
}
