<?php

include_once '/usr/local/share/GRIIDC/php/griidcMailer.php';
require_once '/usr/share/pear/Twig/Autoloader.php';

Twig_Autoloader::register();

$eventHandlerConfig  = parse_ini_file('./eventHandler.ini',true);

function getMessageTemplate($Action)
{
    global $eventHandlerConfig;
    
    if (array_key_exists($Action, $eventHandlerConfig))
    {
        $templateFileName = $eventHandlerConfig[$Action]["mail_template_filepath"];
    
        $messageTemplate = file_get_contents($templateFileName);
        
        if (!$messageTemplate)
        {
            throw new Exception('Could not read template file');
        }
    }
    else
    {
        throw new Exception('Action not found');
    }
    
    return $messageTemplate;
}

function expandTemplate($Template,$Data)
{
    $loader = new Twig_Loader_String();
    $twig = new Twig_Environment($loader);
    
    return $twig->render($Template, $Data);
}

function eventHappened($Action,$Data)
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
    
    try
    {
        $messageTemplate = getMessageTemplate($Action);
        
        $dataManagers = array();
        
        #DMs += getDMsFromUser($data['user'])
        
        #DMs += getDMsFromUDI($data['udi'])
        
        foreach ($dataManagers as $dataManager)
        {
            $mailData = array();
            
            $mailMessage  = expandTemplate($messageTemplate,array('firstname'=>'fred'));
            
            $eventMailer = new griidcMailer(false);
            
            $eventMailer->mailMessage = $mailMessage;
            $eventMailer->mailSubject = 'Where does the title come from?';
        }
    }
    catch (Exception $e) 
    {
        return $e->getMessage();
    }
    
    return true;
    
}

var_dump(eventHappened('dif_saved_and_submitted',array('user'=>'mvandeneijnden','udi'=>'R1.x999.9999:0001')));



?>