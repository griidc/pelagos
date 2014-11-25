<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$user = array('FirstName'=>'Fred','LastName'=>'Flintstone','email'=>'fred@rockstone.net','userId'=>'fflintstone');

$data = array('userId'=>'jweick','udi'=>'R1.x138.078:0019','user'=>$user);

echo '<pre>';

eventHappened('dif_saved_and_submitted',$data);

echo '</pre>';

function getMessageTemplate($Action)
{
    $eventHandlerConfig  = parse_ini_file('./EventHandler.ini', true);

    if (array_key_exists($Action, $eventHandlerConfig)) {
        $templateFileName = $eventHandlerConfig[$Action]["mail_template_filepath"];
        $subject = $eventHandlerConfig[$Action]["subject"];

        $messageTemplate = file_get_contents($templateFileName);

        if (!$messageTemplate) {
            throw new Exception('Could not read template file');
        }
    } else {
        throw new Exception('Action not found');
    }

    return array('messageTemplate'=>$messageTemplate,'subject'=>$subject);
}

function expandTemplate($Template, $Data)
{
    require_once '/usr/share/pear/Twig/Autoloader.php';
    Twig_Autoloader::register();
    $loader = new Twig_Loader_String();
    $twig = new Twig_Environment($loader);

    try {
        return $twig->render($Template, $Data);
    } catch (Exception $e) {
        throw $e;
    }
}

function getRCsByUserId($userId)
{
    require_once 'ResearchConsortia.php';
    require_once 'RIS.php';
    require_once 'DBUtils.php';
    #get Person ID by userId
    $personId = getEmployeeNumberFromUID($userId);
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get RC's by Person ID
    $rcByPersonID = getProjectDetails($RIS_DBH,array("peopleid=$personId"));
    # close database connection
    $RIS_DBH = null;
    
    return $rcByPersonID;
}

function getRCsByUDI($udi)
{
    require_once 'ResearchConsortia.php';
    require_once 'RIS.php';
    require_once 'DBUtils.php';
    #get Project ID by UDI
    $projectid = getRCFromUDI($udi);
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get RC's by Person ID
    $rcByUDI = getProjectDetails($RIS_DBH,array("projectid=$projectid"));
    # close database connection
    $RIS_DBH = null;
    
    return $rcByUDI;
}

function eventHappened($Action, $Data)
{
    $messageData = getMessageTemplate($Action);

    $messageTemplate = $messageData['messageTemplate'];
    $subject = $messageData['subject'];

    require_once 'DataManagers.php';
    $dataManagers = array();
    # check if we have a user ID
    if (array_key_exists('userId', $Data)) {
        $dataManagers = getDMsFromUser($Data['userId']);
    }
    # check if we have an UDI
    if (array_key_exists('udi', $Data)) {
        $getDataManagerID = function ($dataManager) {
            return $dataManager['ID'];
        };
        $dataManagerIDs = array_map($getDataManagerID, $dataManagers);
        foreach (getDMsFromUDI($Data['udi']) as $dataManager) {
            if (!in_array($dataManager['ID'], $dataManagerIDs)) {
                $dataManagers[] = $dataManager;
            }
        }
    }

    foreach ($dataManagers as $dataManager) {
        $mailData = array();
        
        #need: rcbyuserid
        #need: rcbyudi
        
        $rcByUserId = getRCsByUserId($Data['userId']);
        $rcByUDI = getRCsByUDI($Data['udi']);
        
        //var_dump($rcByUserId);
        //var_dump($rcByUDI);
        
        $mailData["data"] = $Data;
        $mailData["dm"] = $dataManager;
        $mailData["rcbyuserid"] = $rcByUserId;
        $mailData["rcbyudi"] = $rcByUDI;
        
        //var_dump($dataManager);

        $mailMessage  = expandTemplate($messageTemplate, $mailData);

        require_once 'stubs/griidcMailerStub.php';
        $eventMailer = new griidcMailer(false);
        $eventMailer->addToUser($dataManager['FirstName'], $dataManager['LastName'], $dataManager['Email']);
        $eventMailer->mailMessage = $mailMessage;
        $eventMailer->mailSubject = $subject;
        $eventMailer->sendMail();
    }

    return true;

}
