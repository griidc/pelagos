<?php

namespace Pelagos;

class Journal
{
    private function addKeyToArrayIfExists(&$source_array, $key, &$target_array)
    {
        if (array_key_exists($key, $source_array)) {
            if ($source_array[$key] != '' and $source_array[$key] != null) {
                $target_array[] = $source_array[$key];
            }
        }
    }
    
    # This function belong in a new class Journals (with an S)
    public function getJournalList()
    {
        require_once $GLOBALS['pelagos']['root'].'/share/php/DBUtils.php';
        $connection = OpenDB('GOMRI_RO');
        
        $query = 'SELECT journal_name, journal_issn FROM udf_get_journals();';
        
        $statementHandler = $connection->prepare($query);
        $rc = $statementHandler->execute();
        if (!$rc) {
            return $statementHandler->errorInfo();
        };
        return $statementHandler->fetchAll();
    }
    
    public function getJournalByName($journalName)
    {
        require_once 'journalDL.php';
        
        //getJournalByName($journalName);
        
        return $journalName;
    }

    public function getJournalByID($journalId)
    {
        require_once 'journalDL.php';
        
        //getJournalById($journalId);
        
        return $journalId;
    }
    
    public function saveJournal($data)
    {
        $parameters = array();
        
        $this->addKeyToArrayIfExists($data, 'journalissn', $parameters);
        $this->addKeyToArrayIfExists($data, 'journalname', $parameters);
        $this->addKeyToArrayIfExists($data, 'journalpublisher', $parameters);
        $this->addKeyToArrayIfExists($data, 'journalstatus', $parameters);
                
        require_once $GLOBALS['pelagos']['root'].'/share/php/DBUtils.php';
        $connection = OpenDB('GOMRI_RO');
        
        if (count($parameters) == 4) {
            $query = 'SELECT udf_insert_journal(?, ?, ?, ?);';
        } else {
            $query = 'SELECT udf_insert_journal(?, ?, ?);';
        }
        $statementHandler = $connection->prepare($query);
        $rc = $statementHandler->execute($parameters);
        if (!$rc) {
            return $statementHandler->errorInfo();
        }
        return $statementHandler->fetchAll();
    }
    
    public function updateJournal($journalData)
    {
        require_once 'journalDL.php';
        
        //updateJournal($journalId);
        
        return $journalData;
    }
    
    public function updateJournalStatus($journalId, $status)
    {
        require_once 'journalDL.php';
        
        //updateJournalStatus($journalId, $status);
        
        return $status;
    }
}
