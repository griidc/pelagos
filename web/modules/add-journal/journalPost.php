<?php

function saveJournal($Data)
{
    require_once $GLOBALS['pelagos']['root'].'/share/php/Pelagos/Journal.php';
    $clsJournal = new Pelagos\Journal();
    
    $result = $clsJournal->saveJournal($Data);
    
    $result['successmesssage'] = "Succesfully saved Journal";
    
    return $result;
}
