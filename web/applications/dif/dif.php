<?php

include 'difDL.php'; //dif DataLayer

include_once '/usr/local/share/GRIIDC/php/ldap.php'; 
include_once '/usr/local/share/GRIIDC/php/griidcMailer.php';

require_once '/usr/share/pear/Twig/Autoloader.php';

global $twig;
$twigloader;

Twig_Autoloader::register();

$twigloader = new Twig_Loader_Filesystem('./templates');
$twig = new Twig_Environment($twigloader,array('autoescape' => false));

if (isset($_POST['function']))
{
    $difFunction = $_POST['function'];
    
    switch ($difFunction)
    {
        case 'loadDIFS':
            $status = $_POST['status'];
            $person = $_POST['person'];
            $showempty = (bool)$_POST['showempty'];
            header('Content-Type: application/json');
            echo getTaskList($status,$person,$showempty);
            break;
        case 'loadTasks':
            header('Content-Type: application/json');
            echo getTasks();
            break;
        case 'fillForm':
            $difUDI = $_POST["udi"];
            header('Content-Type: application/json');
            echo getFormData($difUDI);
            break;
        case 'loadPOCs':
            $PseudoID = $_POST["pseudoid"];
            header('Content-Type: application/json');
            echo getResearchers($PseudoID);
            break;
        case 'saveDIF':
            $formFields = $_POST["formFields"];
            header('Content-Type: application/json');
            echo postDIF($formFields);
            break; 
    }
    exit;
}

function sendSubmitMail($user,$udi,$title,$template)
{
   global $twig;
   
   $difMailer = new griidcMailer(false); 
   
   $userDetails = getUserDetails($user);
   
   $twigdata = $userDetails;
   $twigdata["url"] = 'https://'.$_SERVER[HTTP_HOST].'/dif/?id='.$udi;
   $twigdata["udi"] = $udi;
    
   $message = $twig->render($template, $twigdata);
   
   $difMailer->addToUser($userDetails["firstName"], $userDetails["lastName"], $userDetails["eMail"]);
   $difMailer->mailSubject = $title;

   //$difMailer->donotBCC = true;   #for debug only
   
   $difMailer->mailMessage = $message;
   return $difMailer->sendMail();
}

function mailApprovers($udi,$title,$template)
{
    global $twig;
    $ldap = connectLDAP('triton.tamucc.edu');
    
    $difMailer = new griidcMailer(false); 
    //$difMailer->donotBCC = true;
    
    $twigdata = array();
    
    $twigdata["url"] = 'https://'.$_SERVER[HTTP_HOST].'/dif/?id='.$udi;
    $twigdata["udi"] = $udi;
    
    $message = $twig->render($template, $twigdata);
    
    $members = getAttributes($ldap,"cn=approvers,ou=DIF,ou=applications,dc=griidc,dc=org",array('member'));    
    foreach ($members['member'] as $member)
    {
        $attributes = getAttributes($ldap,$member,array('givenName','sn','mail'));
        if (count($attributes) > 0) {
        			
            if (array_key_exists('givenName',$attributes)) $mailFirstName = $attributes['givenName'][0];
            if (array_key_exists('sn',$attributes)) $mailLastName = $attributes['sn'][0];
            if (array_key_exists('mail',$attributes)) $eMail = $attributes['mail'][0];
        					
            $difMailer->addToUser($mailFirstName, $mailLastName, $eMail);
        }
    }
    
    $difMailer->mailMessage = $message;
    return $difMailer->sendMail();

}

function getUserDetails($userName)
{
    $firstName = '';
    $lastName = '';
    $eMail = '';
    $ldap = connectLDAP('triton.tamucc.edu');
    $baseDN = 'dc=griidc,dc=org';
    $userDNs = getDNs($ldap,$baseDN,"uid=$userName");
    $userDN = $userDNs[0]['dn'];
    if (count($userDNs) > 0) {
            $attributes = getAttributes($ldap,$userDN,array('givenName','sn','mail'));
            if (count($attributes) > 0) {
                    if (array_key_exists('givenName',$attributes)) $firstName = $attributes['givenName'][0];
                    if (array_key_exists('sn',$attributes)) $lastName = $attributes['sn'][0];
                    if (array_key_exists('mail',$attributes)) $eMail = $attributes['mail'][0];
            }
    }
    
    return array('firstName'=>$firstName,'lastName'=>$lastName,'eMail'=>$eMail);
}

function postDIF($fielddata)
{
    $formdata = array();
    
    //var_dump($fielddata);
    
    //exit;
    
    foreach ($fielddata as $field)
    {
        $data[$field["name"]] = $field["value"];
    }
    
    $UDI = $data["udi"];
    $status = (int)$data["status"];
    $title = $data["title"];
    $primarypoc = (int)$data["primarypoc"];
    $secondarypoc = (int)$data["secondarypoc"];
    $abstract = $data["abstract"];
    $pseudoID = (int)($data["task"]);
    $projectID = intval($pseudoID/1024);
    $taskID = $pseudoID - ($projectID*1024);
    $datasettype = $data["dtascii"].'|'.$data["dtuascii"].'|'.$data["dtimages"].'|'.$data["dtnetcdf"].'|'.$data["dtvideo"].'|'.$data["dtgml"].'|'.$data["dtvideoatt"].'|'.$data["dtother"];
    $datasetfor = $data["dfeco"].'|'.$data["dfphys"].'|'.$data["dfatm"].'|'.$data["dfchem"].'|'.$data["dfhumn"].'|'.$data["dfscpe"].'|'.$data["dfeconom"].'|'.$data["dfother"];
    $datasize = $data["size"];
    $approach = $data["appField"].'|'.$data["appSim"].'|'.$data["appLab"].'|'.$data["appLit"].'|'.$data["appRemote"].'|'.$data["appOther"];
    $observation = $data["observation"];
    $startdate = $data["startdate"];
    $enddate = $data["enddate"];
    $geolocation = $data["spatialdesc"];
    $submission = $data["accFtp"].'|'.$data["accTds"].'|'.$data["accErdap"].'|'.$data["accOther"];
    $natarchive = $data["repoNodc"].'|'.$data["repoUsepa"].'|'.$data["repoGbif"].'|'.$data["repoNcbi"].'|'.$data["repoDatagov"].'|'.$data["repoGriidc"].'|'.$data["repoOther"];
    $ethical = $data["privacy"].'|'.$data["privacyother"];
    $remarks = $data["remarks"];
    $datasetUID = time();
    $fundingSourceID = (int)$data["fundsrcid"];
    $gmlText = $data["geoloc"];
    $frmButton = $data["button"];
    $submitted = $data["submitter"];
    $editor = getUID();
    
    if (($status > 1) AND (!isAdmin(getUID()))) {$status = 1;}; #If this happened, someone fiddled with the form.
    
    if (isDIFApprover(getUID()) OR isAdmin(getUID()))
    {
        if ($frmButton == 'approve' AND is) {$status = 2;};
        if ($frmButton == 'reject') {$status = 0;};
        if ($frmButton == 'unlock') {$status = 0;};
    }
    
    if ($frmButton == 'submit') {$status = 1;};
    
    
    if ($fundingSourceID > 0 and $fundingSourceID < 7) {
        $fundingSource = 'Y1';
    }
    else {
        switch ($fundingSourceID) {
            case 7: $fundingSource = 'R1'; break;
            case 8: $fundingSource = 'R2'; break;
            case 9: $fundingSource = 'R3'; break;
            default: $fundingSource = '??';
        }
    }
    
    if (empty($startdate)){$startdate=null;};
    
    if (empty($enddate)){$enddate=null;};
    
    if (empty($gmlText)){$gmlText=null;};
    
    if (empty($submitted) OR ($status < 2 AND ($frmButton != 'reject' AND $frmButton != 'unlock')))
    {$submitted=$editor;};
    
    //$gmlText = '<gml:Point srsName="urn:ogc:def:crs:EPSG::4326"><gml:pos srsDimension="2">27.70323 -97.30042</gml:pos></gml:Point>';
    
    $logname = getPersonID(getUID());
    
    $parameters = array($datasetUID,$UDI,$projectID,$taskID,$title,$primarypoc,$secondarypoc,$abstract,$datasettype,$datasetfor,$datasize,$observation,$approach,$startdate,$enddate,$geolocation,$submission,$natarchive,$ethical,$remarks,$logname,$status,$editor,$gmlText,$fundingSource,$submitted);
    
    $rc = saveDIF($parameters);
    
    $success= is_null($rc[1]) AND ($rc[0]["save_dif"] == 't');
    
    //var_dump($rc);
    
    $sendMail = false;
    
    if ($success)
    {
        if (isset($rc[0]["save_dif"]) AND $rc[0]["save_dif"] != 't')
        {
            #new dif from scratch
            $nudi = $rc[0]["save_dif"];
            $message = '<div><img src="/images/icons/info32.png"><p>You have saved a DIF. This DIF has been given the ID: '.$nudi.'<br>In order to submit your dataset to GRIIDC you must return to this page and submit the DIF for review and approval.</p></div>';
            $msgtitle = 'New DIF Submitted';
        }
        else if ($frmButton == 'approve')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>The application with DIF ID: '.$UDI.' was succesfully approved!</p></div>';
            $msgtitle = 'DIF Approved';
            $sendMail = sendSubmitMail($submitted,$UDI,'GRIIDC DIF Approved','approveMail.html');
        }
        else if ($frmButton == 'reject')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>The application with DIF ID: '.$UDI.' was succesfully rejected!</p></div>';
            $msgtitle = 'DIF Rejected';
        }
        else if ($frmButton == 'save')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Thank you for saving DIF with ID:  '.$UDI.'.<br>Before registering this dataset you must return to this page and submit the dataset information form.</p></div>';
            $msgtitle = 'DIF Submitted';
        }
        else if ($frmButton == 'submit')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Congratulations! You have successfully submitted a DIF to GRIIDC. THE UDI for this dataset is '.$UDI.'.<br>The DIF will now be reviewed by GRIIDC staff and is locked to prevent editing.<br> To unlock your DIF to make changes, you must return to the DIF webpage. You can then load the DIF form you wish to edit from the datasets list and select &quot;Request Unlock&quot; from the bottom of the form.</p></div>';
            $msgtitle = 'DIF Submitted';
            
            $sendMail = sendSubmitMail($submitted,$UDI,'GRIIDC DIF Submitted','submitMail.html');
            mailApprovers($UDI,'DIF Submitted for Approval','reviewMail.html');
            
        }
        else if ($frmButton == 'unlock')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Succesfully unlocked DIF with ID: '.$UDI.'.</p></div>';
            $msgtitle = 'DIF Unlocked';
            $sendMail = sendSubmitMail($submitted,$UDI,'GRIIDC DIF Unlocked','unlocked.html');
            
        }
        else if ($frmButton == 'requnlock')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Your unlock request has been submitted for ID: '.$UDI.'.<br>Your unlock request will be reviewed by GRIIDC staff.<br>You will receive an e-mail when the DIF is unlocked.</p></div>';
            $msgtitle = 'DIF Submitted';
            
            mailApprovers($UDI,'DIF Unlock Request','unlockReq.html');
            
        }
        else
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Succesfully saved DIF with ID: '.$UDI.'.</p></div>';
            $msgtitle = 'DIF Submitted';
        }
    }
    else
    {
        $message = '<div><img src="/images/icons/cancel.png"><p>There was an error! Form NOT submitted.<br>ERROR:'.$rc[0].'</p></div>';
        $msgtitle = 'DIF ERROR';
    }
    
    return json_encode(array('status'=>$rc,'success'=>$success,'message'=>$message,'title'=>$msgtitle,'sendmail'=>$sendMail));
    
    
}

function getResearchers($PseudoID)
{
    $peoples = loadResearchers($PseudoID);
    
    $researchers = array();
    
    foreach ($peoples as $person) {
        $personID = $person['Person_Number'];
        $personName = $person['Person_Name'];
        
        $role = (int) $person['Role_Number'];
        
        // if ($role == 16)
        // {
            // $isPrimary = true;
        // }
        // else
        // {
              $isPrimary = false;
        // }
        
        $researchers[] = array('ID'=>$personID,'Contact'=>$personName,'isPrimary'=>$isPrimary);
    }

    //array_unique($researchers);
    //sort($researchers);

    echo json_encode($researchers);
}

function getTasks()
{
    if (!isAdmin(getUID()))
    {$PersonID=getPersonID(getUID());}

    $diftasks = loadTasks($PersonID);
        
    foreach ($diftasks as $task)
    {
        $maxLength = 200;
        $taskTitle=$task['Task_Title'];
        if (strlen($task['Task_Title']) > $maxLength){
            $taskTitle=substr($taskTitle,0,$maxLength).'...';
        }
        
        $taskID = $task["PseudoTask_Number"];
        $tasks[] = array('Title'=>$taskTitle,'ID'=>$taskID);
    }    
    
    sort($tasks);
    
    echo json_encode($tasks);
}

function getFormData($difID)
{
    $data = loadDIFData($difID);
    $success = true; 
    if (!$data)
    {
        $message = '<div><img src="/images/icons/cancel.png"><p>Couldn\'t find that ID:'.$difID.', no data loaded!</p></div>';
        $msgtitle = 'DIF ERROR';
        $success = false;
    }
    
    $data = $data[0];
    
    $formArr = array();
    
    $formArr = array_merge($formArr,array("udi"=>$data["dataset_udi"]));
    $PseudoTaskNumber = ((int)$data["project_id"] * 1024) + ((int)$data["task_uid"] );
    $formArr = array_merge($formArr,array("task"=>$PseudoTaskNumber));
    $formArr = array_merge($formArr,array("title"=>$data["title"]));
    
    $formArr = array_merge($formArr,array("primarypoc"=>$data["primary_poc"]));
    $formArr = array_merge($formArr,array("secondarypoc"=>$data["secondary_poc"]));
    $formArr = array_merge($formArr,array("abstract"=>$data["abstract"]));
    $formArr = array_merge($formArr,array("size"=>$data["size"]));
    $formArr = array_merge($formArr,array("observation"=>$data["observation"]));
    $formArr = array_merge($formArr,array("historical"=>$data["historic_links"]));
    $formArr = array_merge($formArr,array("remarks"=>$data["remarks"]));
    $formArr = array_merge($formArr,array("status"=>$data["status"]));
    $formArr = array_merge($formArr,array("spatialdesc"=>$data["geo_location"]));
    $formArr = array_merge($formArr,array("geoloc"=>$data["the_geom"]));
    $formArr = array_merge($formArr,array("projectid"=>$data["project_id"]));
    $formArr = array_merge($formArr,array("taskid"=>$data["task_uid"]));
    $formArr = array_merge($formArr,array("submitter"=>$data["submitted_by"]));
    
    $dataSetType = preg_split ("/\|/",$data["dataset_type"]);

    $formArr = array_merge($formArr,array("dtascii"=>($dataSetType[0]=='Structured, Generic Text/ASCII File (CSV, TSV)')));
    $formArr = array_merge($formArr,array("dtuascii"=>($dataSetType[1]=='Unstructured, Generic Text/ASCII File (TXT)')));
    $formArr = array_merge($formArr,array("dtimages"=>($dataSetType[2]=='Images')));
    $formArr = array_merge($formArr,array("dtnetcdf"=>($dataSetType[3]=='CDF/netCDF')));
    $formArr = array_merge($formArr,array("dtvideo"=>($dataSetType[4]=='Video')));
    $formArr = array_merge($formArr,array("dtgml"=>($dataSetType[6]=='GML/XML Structured')));
    $formArr = array_merge($formArr,array("dtvideoatt"=>$dataSetType[5]));
    $formArr = array_merge($formArr,array("dtother"=>$dataSetType[7]));
    
    $dataSetFor = preg_split ("/\|/",$data["dataset_for"]);
    
    $formArr = array_merge($formArr,array("dfeco"=>($dataSetFor[0]=='Ecological/Biological')));
    $formArr = array_merge($formArr,array("dfphys"=>($dataSetFor[1]=='Physical Oceanographical')));
    $formArr = array_merge($formArr,array("dfatm"=>($dataSetFor[2]=='Atmospheric')));
    $formArr = array_merge($formArr,array("dfchem"=>($dataSetFor[3]=='Chemical')));
    $formArr = array_merge($formArr,array("dfhumn"=>($dataSetFor[4]=='Human Health')));
    $formArr = array_merge($formArr,array("dfscpe"=>($dataSetFor[5]=='Social/Cultural/Political')));
    $formArr = array_merge($formArr,array("dfeconom"=>($dataSetFor[6]=='Economics')));
    $formArr = array_merge($formArr,array("dfother"=>$dataSetFor[7]));
    
    $dataSetProc = preg_split ("/\|/",$data["approach"]);
    
    $formArr = array_merge($formArr,array("appField"=>($dataSetProc[0]=='Field Sampling')));
    $formArr = array_merge($formArr,array("appSim"=>($dataSetProc[1]=='Simulated or Generated')));
    $formArr = array_merge($formArr,array("appLab"=>($dataSetProc[2]=='Labratory')));
    $formArr = array_merge($formArr,array("appLit"=>($dataSetProc[3]=='Literature Based')));
    $formArr = array_merge($formArr,array("appRemote"=>($dataSetProc[4]=='Remote Sensing')));
    $formArr = array_merge($formArr,array("appOther"=>$dataSetFor[5]));
    
    $formArr = array_merge($formArr,array("startdate"=>$data["start_date"])); 
    $formArr = array_merge($formArr,array("enddate"=>$data["end_date"])); 
    
    $formArr = array_merge($formArr,array("metaeditor"=>$data["meta_editor"])); 
    
    $metadataStandards = preg_split ("/\|/",$data["meta_standards"]);
    
    $formArr = array_merge($formArr,array("msiso"=>($metadataStandards[0]=='ISO19115')));
    $formArr = array_merge($formArr,array("msfgdc"=>($metadataStandards[1]=='CSDGM')));
    $formArr = array_merge($formArr,array("msdublin"=>($metadataStandards[2]=='DUBLIN')));
    $formArr = array_merge($formArr,array("mseco"=>($metadataStandards[3]=='EML')));
    $formArr = array_merge($formArr,array("msother"=>$metadataStandards[4]));
    
    $accessPoint = preg_split ("/\|/",$data["point"]);
    
    $formArr = array_merge($formArr,array("accFtp"=>($accessPoint[0]=='FTP')));
    $formArr = array_merge($formArr,array("accTds"=>($accessPoint[1]=='TDS')));
    $formArr = array_merge($formArr,array("accErdap"=>($accessPoint[2]=='ERDAP')));
    $formArr = array_merge($formArr,array("accOther"=>$accessPoint[3]));
    
    $dataCenter = preg_split ("/\|/",$data["national"]);
    
    $formArr = array_merge($formArr,array("repoNodc"=>($dataCenter[0]=='National Oceanographic Data Center')));
    $formArr = array_merge($formArr,array("repoUsepa"=>($dataCenter[1]=='US EPA Storet')));
    $formArr = array_merge($formArr,array("repoGbif"=>($dataCenter[2]=='Global Biodiversity Information Facility')));
    $formArr = array_merge($formArr,array("repoNcbi"=>($dataCenter[3]=='National Center for Biotechnology Information')));
    $formArr = array_merge($formArr,array("repoDatagov"=>($dataCenter[4]=='Data.gov Dataset Management System')));
    $formArr = array_merge($formArr,array("repoGriidc"=>($dataCenter[5]=='Gulf of Mexico Research Initiative Information and Data Cooperative (GRIIDC)')));
    $formArr = array_merge($formArr,array("repoOther"=>$dataCenter[6]));
    
    $ethical = preg_split ("/\|/",$data["ethical"]);
    
    $formArr = array_merge($formArr,array("privacy"=>$ethical[0]));
    $formArr = array_merge($formArr,array("privacyother"=>$ethical[1]));
    
    //$formArr = array_merge($formArr,array("isadmin"=>$isadmin));
    
    return json_encode(array('data'=>$formArr,'success'=>$success,'message'=>$message,'title'=>$msgtitle));
}

//echo getTaskList();

function getTaskList($Status=null,$PersonID=null,$ShowEmpty=true)
{
    if (!isAdmin(getUID()))
    {$PersonID=getPersonID(getUID());}
    
    $listArray = array();

    $tasks = loadTasks($PersonID);
    
    foreach ($tasks as $task)
    {
        $taskTitle = $task['Task_Title'];
        //$projectID = $task['projectid'];
        $pseudoID = $task['PseudoTask_Number'];
        $taskdata = loadTaskData($pseudoID,$Status);
        $childArr = array();
        
        //var_dump($taskdata);
        
        if ($taskdata[0]["Title"] != null)
        {
            foreach ($taskdata as $dif) 
            {
                //var_dump($row);
                
                //exit;
                $status = $dif["Access_Status"];
                $title = $dif["Title"];
                $difudi = $dif["UDI"];
    
                switch ($status)
                {
                    case null:
                    $icon =  '/dataset-monitoring/includes/images/x.png';
                    break;
                    case 'Open':
                    $icon = '/dataset-monitoring/includes/images/x.png';
                    break;
                    case 'Locked':
                    $icon = '/dataset-monitoring/includes/images/triangle_yellow.png';
                    break;
                    case 'Approved':
                    $icon =  '/dataset-monitoring/includes/images/check.png';
                    break;
                }
                
                $childArr[] = array("text"=>"[$difudi] $title","icon"=>$icon,"li_attr"=>array("title"=>$title),"a_attr"=>array("onclick"=>"getNode('$difudi');"));
                
            }
            $listArray[] = array("text"=>$taskTitle,"icon"=>"/dif/images/folderopen.gif","state"=>array("opened"=>true),"children"=>$childArr,"li_attr"=>array("title"=>$taskTitle));  
        }
        else
        {
            if ($ShowEmpty)
            {
                $listArray[] = array("text"=>$taskTitle,"icon"=>"/dif/images/nofolder.png","children"=>array("text"=>""),"li_attr"=>array("title"=>$taskTitle));       
            }
        }
        
    }
    
    if ($tasks == null)
    {
        $listArray[] = array("text"=>"None found","icon"=>"/dif/images/griidc_fav.png","state"=>array("opened"=>true),"li_attr"=>array("title"=>$taskTitle));  
    }
    
    return json_encode($listArray);
}

function showDIFForm()
{
    global $twig;
   //$helpText = getHelpText('DIF');
    
    $isadmin = isAdmin(getUID());
    $isDManager = isDataManager(getUID());
    $isDIFApprover = isDIFApprover(getUID());
    
    $personid = getPersonID(getUID());
    if ($personid == 0) {$personid='';};
    
    // echo "isadmin:$isadmin<br>";
    // echo "isDManager:$isDManager<br>";
    // echo "isDIFApprover:$isDIFApprover<br>";
    
    $twigdata = array('isadmin'=>$isadmin,'isdmanager'=>$isDManager,'isdifapprover'=>$isDIFApprover,'personid'=>$personid);
   
    echo $twig->render('difForm.html', $twigdata); 
}

function getPersonID($UserName)
{
    $ldap = connectLDAP('triton.tamucc.edu');
    $baseDN = 'dc=griidc,dc=org';
    $uid = $UserName;
    if (isset($uid)) {
        $submittedby = 0;
        $userDNs = getDNs($ldap,$baseDN,"uid=$uid");
        $userDN = $userDNs[0]['dn'];
        if (count($userDNs) > 0) {
            $attributes = getAttributes($ldap,$userDN,array('givenName','sn','employeeNumber'));
            if (count($attributes) > 0) {
                if (array_key_exists('givenName',$attributes)) $firstName = $attributes['givenName'][0];
                if (array_key_exists('sn',$attributes)) $lastName = $attributes['sn'][0];
                if (array_key_exists('employeeNumber',$attributes)) (int)$submittedby = $attributes['employeeNumber'][0];
            }
        }
    }
    return $submittedby;
}

function isDIFApprover($UserName) 
{
    $admin = false;
    if ($UserName) 
    {
        $ldap = ldap_connect('ldap://triton.tamucc.edu');
        $adminsResult = ldap_search($ldap, "cn=approvers,ou=DIF,ou=applications,dc=griidc,dc=org", '(objectClass=*)', array("member"));
        $admins = ldap_get_entries($ldap, $adminsResult);
        for ($i=0;$i<$admins[0]['member']['count'];$i++) {
            if ("uid=$UserName,ou=members,ou=people,dc=griidc,dc=org" == $admins[0]['member'][$i]) {
                $admin = true;
            }
        }
    }
    return $admin;
}

function isAdmin($UserName) 
{
    $admin = false;
    if ($UserName) 
    {
        $ldap = ldap_connect('ldap://triton.tamucc.edu');
        $adminsResult = ldap_search($ldap, "cn=administrators,ou=DIF,ou=applications,dc=griidc,dc=org", '(objectClass=*)', array("member"));
        $admins = ldap_get_entries($ldap, $adminsResult);
        for ($i=0;$i<$admins[0]['member']['count'];$i++) {
            if ("uid=$UserName,ou=members,ou=people,dc=griidc,dc=org" == $admins[0]['member'][$i]) {
                $admin = true;
            }
        }
    }
    return $admin;
}

function getUID() {
    global $user;
    if (isset($user->name))
    {
        if (array_key_exists('as_user',$_GET) and isAdmin($user->name)) {
            return $_GET['as_user'];
        }
        return $user->name;
    }
    else
    {return null;}
}

function isDataManager($UserName)
{
    $admin = false;
    if ($UserName) 
    {
        $ldap = ldap_connect('ldap://triton.tamucc.edu');
        $adminsResult = ldap_search($ldap, 'ou=groups,dc=griidc,dc=org', "(&(member=uid=$UserName,ou=members,ou=people,dc=griidc,dc=org)(cn=administrators))", array("dn"));
        $admins = ldap_get_entries($ldap, $adminsResult);
        
        if (isset($admins[0]))
        {return count($admins[0])>0;}
        else {return false;}
    }

}




?>