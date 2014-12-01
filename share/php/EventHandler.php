<?php

function getMessageTemplate($Action)
{
    $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini',true);
    $templatePath = $GLOBALS['config']['paths']['templates'];

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
    #get Project details by Person ID
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
    #get Project details by Project ID
    $rcsByUDI = getProjectDetails($RIS_DBH, array("projectid=$projectid"));
    # close database connection
    $RIS_DBH = null;

    return $rcByUDI[0];
}

function getRCsByPeopleID($RISUserId)
{
    require_once 'ResearchConsortia.php';
    require_once 'RIS.php';
    require_once 'DBUtils.php';
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get RC's by Person ID
    $rcsByPeopleId = array();
    foreach (getRCsFromRISUser($RIS_DBH, $RISUserId) as $projectid)
    {
        #get Project details by Project ID for each ID
        $projectDetails = getProjectDetails($RIS_DBH, array("projectid=$projectid"));
        $rcsByPeopleId[] = $projectDetails[0];
    }
    
    # close database connection
    $RIS_DBH = null;
    
    return $rcsByPeopleId[0];
}

function getDMsFromPeopleID($peopleId)
{
    require_once 'ResearchConsortia.php';
    require_once 'RIS.php';
    require_once 'DBUtils.php';
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get DM's by Person ID
    $dmByPeopleID = getDMsFromRISUser($RIS_DBH, $peopleId);
    # close database connection
    $RIS_DBH = null;
    
    return $dmByPeopleID; 
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
        
        $getDataManagerID = function ($dataManager) {
            return $dataManager['id'];
        };
        # check if we have a user ID
        if (array_key_exists('userId', $Data)) {
            $dataManagers = getDMsFromUser($Data['userId']);
            $rcByUserId = getRCsByUserId($Data['userId']);
        }
        # check if we have an UDI
        if (array_key_exists('udi', $Data)) {
            $dataManagerIDs = array_map($getDataManagerID, $dataManagers);
            foreach (getDMsFromUDI($Data['udi']) as $dataManager) {
                if (!in_array($dataManager['id'], $dataManagerIDs)) {
                    $dataManagers[] = $dataManager;
                }
            }
            $rcByUDI = getRCsByUDI($Data['udi']);
        }
        # check to see if Person ID is given
        if (array_key_exists('RISUserId', $Data)) {
            $dataManagerIDs = array_map($getDataManagerID, $dataManagers);
            foreach (getDMsFromPeopleID($Data['RISUserId']) as $dataManager) {
                if (!in_array($dataManager['id'], $dataManagerIDs)) {
                    $dataManagers[] = $dataManager;
                }
            } 
            $rcByUserId = getRCsByPeopleID($Data['RISUserId']);
        }
    }

    $getRCTitle = function ($researchConsortia) {
        return $researchConsortia['Title'];
    };

    foreach ($dataManagers as $dataManager) {
        $mailData = array();

        $mailData["data"] = $Data;
        $mailData["dm"] = $dataManager;
        if (isset($rcByUserId)) {
            $mailData['data']['user']['RCs'] = array_map($getRCTitle, $rcByUserId);
        }
        if (isset($rcByUDI)) {
            $mailData["rcbyudi"] = $rcByUDI;
        }

        $mailMessage  = expandTemplate($messageTemplate, $mailData);

        require_once 'griidcMailer.php';
        $eventMailer = new griidcMailer(false);
        $eventMailer->addToUser($dataManager['firstName'], $dataManager['lastName'], $dataManager['email']);
        $eventMailer->mailMessage = $mailMessage;
        $eventMailer->mailSubject = $subject;
        $eventMailer->sendMail();
    }

    return true;
}
