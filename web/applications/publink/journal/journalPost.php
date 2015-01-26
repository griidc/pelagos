<?php

function saveJournal($Data)
{
    require_once 'journal.php';
    $clsJournal = new Pelagos\Journal();
    
    $result = $clsJournal->saveJournal($Data);
    
    $result['successmesssage'] = "Succesfully saved Journal";
    
    return $result;
}
