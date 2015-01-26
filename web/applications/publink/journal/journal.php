<?php

class Journal 
{
    private function addKeyToArrayIfExists(&$source_array, $key, &$target_array)
    {
        if (array_key_exists($key, $source_array)) {
            if ($source_array[$key] != '' AND $source_array[$key] != null) {
                $target_array[] = $source_array[$key];
            }
        }
    }
    
    public function getJournalList()
    {
        require_once 'journalDL.php';
        
        return getJournalList();
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
        require_once 'journalDL.php';
        
        $parameters = array();
        
        $this->addKeyToArrayIfExists($data,'journalissn',$parameters);
        $this->addKeyToArrayIfExists($data,'journalname',$parameters);
        $this->addKeyToArrayIfExists($data,'journalpublisher',$parameters);
        $this->addKeyToArrayIfExists($data,'journalstatus',$parameters);
                
        $result =  insertJournal($parameters);
        
        return $result;
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

?>