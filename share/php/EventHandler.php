<?php
// @codingStandardsIgnoreFile

function getMessageTemplate($Action)
{
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $templatePath = $config['paths']['templates'];
    $iniPath = $config['paths']['conf'];
    $iniFileName = $iniPath.'/'.'EventHandler.ini';

    $eventHandlerConfig  = parse_ini_file($iniFileName, true);

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
    require_once 'Twig/Autoloader.php';
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

function getRCsByUDI($udi) # return Array
{
    require_once 'datasets.php';
    require_once 'RIS.php';
    require_once 'DBUtils.php';
    $GOMRI_DBH = openDB('GOMRI_RO');
    #get Project ID by UDI
    $projectid = getProjectIdFromUdi($GOMRI_DBH, $udi);
    if (!$projectid) {
        return array();
    }
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get Project details by Project ID
    $rcsByUDI = getProjectDetails($RIS_DBH, array("projectid=$projectid"));
    # close database connections
    $RIS_DBH = null;
    $GOMRI_DBH = null;

    return $rcsByUDI[0];
}

function getRCsByPeopleID($risUserId)
{
    require_once 'ResearchConsortia.php';
    require_once 'RIS.php';
    require_once 'DBUtils.php';
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get RC's by Person ID
    $rcsByPeopleId = array();
    foreach (getRCsFromRISUser($RIS_DBH, $risUserId) as $projectid) {
        #get Project details by Project ID for each ID
        $projectDetails = getProjectDetails($RIS_DBH, array("projectid=$projectid"));
        if (count($projectDetails) == 0) {
            # skip projects that we are unable to retrieve information for
            continue;
        }
        $rcsByPeopleId[] = $projectDetails[0];
    }

    # close database connection
    $RIS_DBH = null;

    return $rcsByPeopleId;
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

function geteMailUserDetailsFromUserId($risUserId)
{
    require_once 'RIS.php';
    require_once 'DBUtils.php';
    # open a database connetion to RIS
    $RIS_DBH = openDB('RIS_RO');
    #get DM's by Person ID
    $peopleDetailsByPeopleID = getPeopleDetails($RIS_DBH, array("peopleid=$risUserId"));
    # close database connection
    $RIS_DBH = null;

    $eMailUserDetails = array();

    if (is_array($peopleDetailsByPeopleID) and count($peopleDetailsByPeopleID) > 0) {
        foreach ($peopleDetailsByPeopleID as $people) {
            $eMailUserDetails['firstName'] = $people['FirstName'];
            $eMailUserDetails['lastName'] = $people['LastName'];
            $eMailUserDetails['email'] = $people['Email'];
        }
    }

    return $eMailUserDetails;
}

function eventHappened($Action, $Data)
{
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $iniPath = $config['paths']['conf'];
    $iniFileName = $iniPath.'/'.'EventHandler.ini';
    $eventHandlerConfig  = parse_ini_file($iniFileName, true);

    if (!array_key_exists($Action, $eventHandlerConfig)) {
        throw new Exception('Action not found');
    }

    $actions = preg_split('/,/', $eventHandlerConfig[$Action]['action']);

    foreach ($actions as $action) {
        #Take an action according to the event type/action
        switch ($action) {
            case "emaildm":
                emailDM($Action, $Data);
                break;
            case "sendmail":
                emailUser($Action, $Data);
                break;
            case "emaildoiapprovers":
                emailDOIApprovers($Action, $Data);
                break;
        }
    }
}

function getEmailUsersFromLDAPGroup($ldapGroup)
{
    $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
    $GLOBALS['config'] = array_merge(
        $GLOBALS['config'],
        parse_ini_file($GLOBALS['config']['paths']['conf'].'/ldap.ini', true)
    );

    $users = array();

    require_once 'ldap.php';

    $members = getGroupMembers($ldapGroup);
    $ldap = connectLDAP($GLOBALS['config']['ldap']['server']);

    foreach ($members as $member) {
        $attributes = getAttributes($ldap, $member, array('givenName', 'sn', 'mail'));
        if (count($attributes) > 0) {
            if (array_key_exists('givenName', $attributes)) {
                $mailFirstName = $attributes['givenName'][0];
            }
            if (array_key_exists('sn', $attributes)) {
                $mailLastName = $attributes['sn'][0];
            }
            if (array_key_exists('mail', $attributes)) {
                $eMail = $attributes['mail'][0];
            }

            $users[] = array("firstName"=>$mailFirstName,"lastName"=>$mailLastName,"email"=>$eMail);
        }
    }

    return $users;
}

function emailDOIApprovers($Action, $Data)
{
    $grp = "cn=approvers,ou=DOI,ou=Pelagos,ou=applications,dc=griidc,dc=org";
    $approvers = getEmailUsersFromLDAPGroup($grp);

    $messageData = getMessageTemplate($Action);

    $messageTemplate = $messageData['messageTemplate'];
    $subject = $messageData['subject'];

    foreach ($approvers as $approver) {
        $mailData = array();

        $mailData["data"] = $Data;
        $mailData["apprv"] = $approver;

        $mailMessage  = expandTemplate($messageTemplate, $mailData);

        require_once 'griidcMailer.php';
        $eventMailer = new griidcMailer(false);
        $eventMailer->addToUser($approver['firstName'], $approver['lastName'], $approver['email']);
        $eventMailer->mailMessage = $mailMessage;
        $eventMailer->mailSubject = $subject;
        $eventMailer->sendMail();
    }
}

function emailUser($Action, $Data)
{
    $messageData = getMessageTemplate($Action);

    $messageTemplate = $messageData['messageTemplate'];
    $subject = $messageData['subject'];

    $mailData = array();

    $mailData["data"] = $Data;

    #make sure user exists
    if (is_array($Data) and (array_key_exists('userId', $Data) or array_key_exists('risUserId', $Data))) {
        require_once 'ldap.php';

        if (!array_key_exists('risUserId', $Data)) {
            $risUserId = getEmployeeNumberFromUID($Data['userId']);
        } else {
            $risUserId = $Data['risUserId'];
        }

        $user = geteMailUserDetailsFromUserId($risUserId);

        $mailData["user"] = $user;

        $mailMessage  = expandTemplate($messageTemplate, $mailData);

        if (count($user) > 0) {
            require_once 'griidcMailer.php';
            $eventMailer = new griidcMailer(false);
            $eventMailer->addToUser($user['firstName'], $user['lastName'], $user['email']);
            $eventMailer->mailMessage = $mailMessage;
            $eventMailer->mailSubject = $subject;
            $eventMailer->sendMail();
        }
    }

    return true;
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
        if (array_key_exists('risUserId', $Data)) {
            $dataManagerIDs = array_map($getDataManagerID, $dataManagers);
            foreach (getDMsFromPeopleID($Data['risUserId']) as $dataManager) {
                if (!in_array($dataManager['id'], $dataManagerIDs)) {
                    $dataManagers[] = $dataManager;
                }
            }
            $rcByUserId = getRCsByPeopleID($Data['risUserId']);
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
