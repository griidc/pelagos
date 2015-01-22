<?php

//include 'formHandler.php';

function saveJournal($Data)
{
    require_once 'journal.php';
    $clsJournal = new Journal();
    
    $result = $clsJournal->saveJournal($Data);
    
    $result['successmesssage'] = "Succesfully saved Journal";
    
    return $result;
}

function rejectJournal($Data)
{
    // Test only!
    $json = '{"success":false,"title":"No good!","message":"Didn\'t like your data!","status":200,"statusText":"OK"}';
    
    return (array) json_decode($json);
    //return '{"success":true,"title":"Success!","message":"Form rejected.","status":200,"statusText":"OK"}';
}





?>
