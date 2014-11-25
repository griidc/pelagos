<?php

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

    return $twig->render($Template, $Data);
}

function eventHappened($Action, $Data)
{
    #Placeholder

    /*
        $template = get_message_text_template_from_id($action_id)
        $DMs = array()
        $DMs += getDMsFromUser($data['user'])
        $DMs += getDMsFromUDI($data['udi'])
        loop($DMs) {
        $to = $DM['email']
        $message = expand_template($template,$data+$DM);
        mail($to,$message)
    */

    $messageData = getMessageTemplate($Action);

    $messageTemplate = $messageData['messageTemplate'];
    $subject = $messageData['subject'];

    require_once 'DataManagers.php';
    $dataManagers = array();
    # check if we have a user ID
    if (array_key_exists('uid', $Data)) {
        $dataManagers = getDMsFromUser($Data['uid']);
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

        $mailMessage  = expandTemplate($messageTemplate, array('firstname' => $dataManager['FirstName']));

        require_once 'griidcMailer.php';
        $eventMailer = new griidcMailer(false);
        $eventMailer->addToUser($dataManager['FirstName'], $dataManager['LastName'], $dataManager['Email']);
        $eventMailer->mailMessage = $mailMessage;
        $eventMailer->mailSubject = $subject;
        $eventMailer->sendMail();
    }

    return true;

}
