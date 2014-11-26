<?php

function getMessageTemplate($Action)
{
    $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini',true);
    $templatePath = $GLOBALS['config']['templates'];

    $eventHandlerConfig  = parse_ini_file('/etc/opt/pelagos/EventHandler.ini', true);

    if (array_key_exists($Action, $eventHandlerConfig)) {
        $templateFileName = $templatePath.'/'.$eventHandlerConfig[$Action]["mail_template_filename"];
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
    require_once 'ldap.php';
    #get Person ID by userId
    $personId = getEmployeeNumberFromUID($userId);
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get RC's by Person ID
    $rcByPersonID = getProjectDetails($RIS_DBH, array("peopleid=$personId"));
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
    $rcByUDI = getProjectDetails($RIS_DBH, array("projectid=$projectid"));
    # close database connection
    $RIS_DBH = null;

    return $rcByUDI;
}

function eventHappened($Action, $Data)
{
    emailDM($Action, $Data);
}

function emailDM($Action, $Data)
{
    $messageData = getMessageTemplate($Action);

    $messageTemplate = $messageData['messageTemplate'];
    $subject = $messageData['subject'];

    require_once 'DataManagers.php';
    $dataManagers = array();
    
    if (is_array($Data)) {
        # check if we have a user ID
        if (array_key_exists('userId', $Data)) {
            $dataManagers = getDMsFromUser($Data['userId']);
            $rcByUserId = getRCsByUserId($Data['userId']);
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
            $rcByUDI = getRCsByUDI($Data['udi']);
        }
    }

    foreach ($dataManagers as $dataManager) {
        $mailData = array();

        $mailData["data"] = $Data;
        $mailData["dm"] = $dataManager;
        if (isset($rcByUserId)) {
            $mailData["rcbyuserid"] = $rcByUserId;
        }
        if (isset($rcByUDI)) {
            $mailData["rcbyudi"] = $rcByUDI;
        }

        $mailMessage  = expandTemplate($messageTemplate, $mailData);

        require_once 'griidcMailer.php';
        $eventMailer = new griidcMailer(false);
        $eventMailer->addToUser($dataManager['FirstName'], $dataManager['LastName'], $dataManager['Email']);
        $eventMailer->mailMessage = $mailMessage;
        $eventMailer->mailSubject = $subject;
        $eventMailer->sendMail();
    }

    return true;
}
