<?php
// @codingStandardsIgnoreFile

$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
$GLOBALS['config'] = array_merge($GLOBALS['config'], parse_ini_file($GLOBALS['config']['paths']['conf'].'/ldap.ini', true));

set_include_path('../../../share/php' . PATH_SEPARATOR . get_include_path());

require_once 'aliasIncludes.php';
require_once 'difDL.php'; //dif DataLayer
require_once 'ldap.php';
require_once 'griidcMailer.php';
require_once 'dif-registry.php';
require_once 'EventHandler.php';

global $twig;
$twigloader;

$twigloader = new \Twig_Loader_Filesystem('./templates');
$twig = new \Twig_Environment($twigloader,array('autoescape' => false));

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
            //echo getTaskList($status,$person,$showempty);
            echo getDIFS($person,$status);
            break;
        case 'loadTasks':
            header('Content-Type: application/json');
            $person = $_POST['person'];
            echo getTaskOptions($person);
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
    $ldap = connectLDAP($GLOBALS['config']['ldap']['server']);

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

    $difMailer->mailSubject = $title;
    $difMailer->mailMessage = $message;
    return $difMailer->sendMail();

}

function getUserDetails($userName)
{
    $firstName = '';
    $lastName = '';
    $eMail = '';
    $ldap = connectLDAP($GLOBALS['config']['ldap']['server']);
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
    $editor = getUserID();

    if (($status > 1) AND (!isUserAdmin(getUserID()))) {$status = 1;}; #If this happened, someone fiddled with the form.

    if (isDIFApprover(getUserID()) OR isUserAdmin(getUserID()))
    {
        if ($frmButton == 'approve' AND is) {$status = 2;};
        if ($frmButton == 'reject') {$status = 0;};
        if ($frmButton == 'unlock') {$status = 0;};
    }

    if ($frmButton == 'submit') {$status = 1;};

    if ($fundingSourceID != 0 and $fundingSourceID != null) {
        if ($fundingSourceID > 0 and $fundingSourceID < 7) {
            $fundingSource = 'Y1';
        }
        else {
            switch ($fundingSourceID) {
                case 7: $fundingSource = 'R1'; break;
                case 8: $fundingSource = 'R2'; break;
                case 9: $fundingSource = 'R3'; break;
                case 10: $fundingSource = 'R4'; break;
                case 11: $fundingSource = 'R5'; break;
                case 700: $fundingSource = 'F1'; break;
                default: return json_encode(array('success'=>false,'message'=>'Unknown Funding Source!','title'=>'ERROR'));
            }
        }
    }

    if (empty($startdate)){$startdate=null;};

    if (empty($enddate)){$enddate=null;};

    if (empty($gmlText)){$gmlText=null;};

    if (empty($submitted) OR ($status < 2 AND ($frmButton != 'reject' AND $frmButton != 'unlock')))
    {$submitted=$editor;};

    $logname = getPersonID(getUserID());

    $parameters = array($datasetUID,$UDI,$projectID,$taskID,$title,$primarypoc,$secondarypoc,$abstract,$datasettype,$datasetfor,$datasize,$observation,$approach,$startdate,$enddate,$geolocation,$submission,$natarchive,$ethical,$remarks,$logname,$status,$editor,$gmlText,$fundingSource,$submitted);

    if ((isUserAdmin(getUserID())) OR ($frmButton == 'submit' OR $frmButton == 'save'))
    {
        $rc = saveDIF($parameters);
    }

    $success= ((is_null($rc[1])) AND (($rc[0]["save_dif"] == 't') OR $rc[0]["save_dif"] != 'f'));

    $sendMail = false;

    if ($success)
    {
        if (isset($rc[0]["save_dif"]) AND $rc[0]["save_dif"] != 't')
        {
            #new dif from scratch
            $nudi = $rc[0]["save_dif"];

            if ($status == 0)
            {
                $msgtitle = 'New DIF Created';
                $message = '<div><img src="/images/icons/info32.png"><p>You have saved a DIF. This DIF has been given the ID: '.$nudi.'<br>In order to submit your dataset to GRIIDC you must return to this page and submit the DIF for review and approval.</p></div>';

                $userData = getUserDetails($editor);
                $eventData = array('udi'=>$nudi,'userId'=>$editor,'user'=>$userData);
                eventHappened('dif_saved_but_not_submitted',$eventData);
            }
            else
            {
                $msgtitle = 'New DIF Submitted';

                $message = '<div><img src="/images/icons/info32.png">'.
                            '<p>Congratulations! You have successfully submitted a DIF to GRIIDC. The UDI for this dataset is '. $nudi.'.'.
                            '<br>The DIF will now be reviewed by GRIIDC staff and is locked to prevent editing. To make changes'.
                            '<br>to your DIF, please email GRIIDC at griidc@gomri.org with the UDI for your dataset.'.
                            '<br>Please note that you will receive an email notification when your DIF is approved.</p></div>';
                $msgtitle = 'DIF Submitted';

                $sendMail = sendSubmitMail($submitted,$nudi,'GRIIDC DIF Submitted','submitMail.html');
                mailApprovers($nudi,'DIF Submitted for Approval','reviewMail.html');

                $userData = getUserDetails($submitted);
                $eventData = array('udi'=>$nudi,'userId'=>$submitted,'user'=>$userData);
                eventHappened('dif_saved_and_submitted',$eventData);
            }

        }
        else if ($frmButton == 'approve')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>The application with DIF ID: '.$UDI.' was successfully approved!</p></div>';
            $msgtitle = 'DIF Approved';
            $sendMail = sendSubmitMail($submitted,$UDI,'GRIIDC DIF Approved','approveMail.html');

            $userData = getUserDetails($submitted);
            $eventData = array('udi'=>$UDI,'userId'=>$submitted,'user'=>$userData);
            eventHappened('dif_approved',$eventData);
        }
        else if ($frmButton == 'reject')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>The application with DIF ID: '.$UDI.' was successfully rejected!</p></div>';
            $msgtitle = 'DIF Rejected';
        }
        else if ($frmButton == 'save')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Thank you for saving DIF with ID:  '.$UDI.'.<br>Before registering this dataset you must return to this page and submit the dataset information form.</p></div>';
            $msgtitle = 'DIF Submitted';

            $userData = getUserDetails($editor);
            $eventData = array('udi'=>$UDI,'userId'=>$editor,'user'=>$userData);
            eventHappened('dif_saved_but_not_submitted',$eventData);
        }
        else if ($frmButton == 'submit')
        {
            $message = '<div><img src="/images/icons/info32.png">'.
                            '<p>Congratulations! You have successfully submitted a DIF to GRIIDC. The UDI for this dataset is '. $UDI.'.'.
                            '<br>The DIF will now be reviewed by GRIIDC staff and is locked to prevent editing. To make changes'.
                            '<br>to your DIF, please email GRIIDC at griidc@gomri.org with the UDI for your dataset.'.
                            '<br>Please note that you will receive an email notification when your DIF is approved.</p></div>';
            $msgtitle = 'DIF Submitted';

            $sendMail = sendSubmitMail($submitted,$UDI,'GRIIDC DIF Submitted','submitMail.html');
            mailApprovers($UDI,"DIF:$UDI Submitted for Approval",'reviewMail.html');

            $userData = getUserDetails($submitted);
            $eventData = array('udi'=>$UDI,'userId'=>$submitted,'user'=>$userData);
            eventHappened('dif_saved_and_submitted',$eventData);

        }
        else if ($frmButton == 'unlock')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Successfully unlocked DIF with ID: '.$UDI.'.</p></div>';
            $msgtitle = 'DIF Unlocked';
            $sendMail = sendSubmitMail($submitted,$UDI,'GRIIDC DIF Unlocked','unlocked.html');

            $userData = getUserDetails($submitted);
            $eventData = array('udi'=>$UDI,'userId'=>$submitted,'user'=>$userData);
            eventHappened('dif_unlock_request_approved',$eventData);

        }
        else if ($frmButton == 'requnlock')
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Your unlock request has been submitted for ID: '.$UDI.'.<br>Your unlock request will be reviewed by GRIIDC staff.<br>You will receive an e-mail when the DIF is unlocked.</p></div>';
            $msgtitle = 'DIF Submitted';

            mailApprovers($UDI,"DIF:$UDI Unlock Request",'unlockReq.html');

            $userData = getUserDetails($editor);
            $eventData = array('udi'=>$UDI,'userId'=>$editor,'user'=>$userData);
            eventHappened('dif_unlock_requested',$eventData);
        }
        else
        {
            $message = '<div><img src="/images/icons/info32.png"><p>Successfully saved DIF with ID: '.$UDI.'.</p></div>';
            $msgtitle = 'DIF Submitted';
        }
    }
    else
    {
        if (is_array($rc[0]))
        {
            $error = 'unknown';
        }
        else
        {
            $error = $rc[0];
        }

        $message = '<div><img src="/images/icons/cancel.png"><p>There was an error! Form NOT submitted.<br>ERROR:'.$error.'</p></div>';
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

function getTasks_OLD()
{
    if (!isUserAdmin(getUserID()))
    {$PersonID=getPersonID(getUserID());}

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
    $formArr = array_merge($formArr,array("appOther"=>$dataSetProc[5]));

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

    //$formArr = array_merge($formArr,array("isUserAdmin"=>$isUserAdmin));

    return json_encode(array('data'=>$formArr,'success'=>$success,'message'=>$message,'title'=>$msgtitle));
}

//echo getTaskList();

function getTaskList($Status=null,$PersonID=null,$ShowEmpty=true)
{
    if (!isUserAdmin(getUserID()))
    {$PersonID=getPersonID(getUserID());}

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

    $isAdmin = isUserAdmin(getUserID());
    $isDManager = isDataManager(getUserID());
    $isDIFApprover = isDIFApprover(getUserID());

    $personid = getPersonID(getUserID());
    if ($personid == 0) {$personid='';};

    // echo "isUserAdmin:$isUserAdmin<br>";
    // echo "isDManager:$isDManager<br>";
    // echo "isDIFApprover:$isDIFApprover<br>";

    $twigdata = array('isadmin'=>$isAdmin,'isdmanager'=>$isDManager,'isdifapprover'=>$isDIFApprover,'personid'=>$personid);

    echo $twig->render('difForm.html', $twigdata);
}

function getPersonID($UserName)
{
    $ldap = connectLDAP($GLOBALS['config']['ldap']['server']);
    $baseDN = 'dc=griidc,dc=org';
    $uid = $UserName;
    if (isset($uid)) {
        $submittedby = null;
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
        $ldap = ldap_connect('ldap://'.$GLOBALS['config']['ldap']['server']);
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

function isUserAdmin($UserName)
{
    $admin = false;
    if ($UserName)
    {
        $ldap = ldap_connect('ldap://'.$GLOBALS['config']['ldap']['server']);
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

function isDataManager($UserName)
{
    $admin = false;
    if ($UserName)
    {
        $ldap = ldap_connect('ldap://'.$GLOBALS['config']['ldap']['server']);
        $adminsResult = ldap_search($ldap, 'ou=groups,dc=griidc,dc=org', "(&(member=uid=$UserName,ou=members,ou=people,dc=griidc,dc=org)(cn=administrators))", array("dn"));
        $admins = ldap_get_entries($ldap, $adminsResult);

        if (isset($admins[0]))
        {return count($admins[0])>0;}
        else {return false;}
    }

}


function getRISTasks($personID)
{
    $GLOBALS['pelagos_config']  = parse_ini_file('/etc/opt/pelagos.ini',true);
    $GLOBALS['ldap_config']     = parse_ini_file($GLOBALS['pelagos_config']['paths']['conf'].'/ldap.ini',true);
    define('RPIS_TASK_BASEURL','http://localhost/services/RIS/getTaskDetails.php');

    $ldap = connectLDAP($GLOBALS['ldap_config']['ldap']['server']);
    $baseDN = $GLOBALS['ldap_config']['ldap']['base_dn'];

    $uid = getUserID();
    if (isset($uid)) {
        $submittedby ="";
        $userDNs = getDNs($ldap,$baseDN,"uid=$uid");
        $userDN = $userDNs[0]['dn'];
        if (count($userDNs) > 0) {
            $attributes = getAttributes($ldap,$userDN,array('givenName','sn','employeeNumber'));
            if (count($attributes) > 0) {
                if (array_key_exists('givenName',$attributes)) $firstName = $attributes['givenName'][0];
                if (array_key_exists('sn',$attributes)) $lastName = $attributes['sn'][0];
                if (array_key_exists('employeeNumber',$attributes)) $submittedby = $attributes['employeeNumber'][0];
            }
        }
    }

    # get Tasks from RIS Service
    $tasks = getTasks($ldap,$baseDN,$userDN,$personID);

    return $tasks;
}

function getDIFS($personID,$status)
{
    $tasks = getRISTasks($personID);

    $stuff = displayTaskStatus($tasks,$dbconn);

    sort($stuff);

    return json_encode($stuff);
}

function getTaskOptions($personID)
{
    $rpisTasks = getRISTasks($personID);

    foreach ($rpisTasks as $task)
    {
        $fundingSourceName = (string)$task->Project->FundingSource;

        if (preg_match('/\(([^\)]+)\)/', $fundingSourceName ,$matches)) {
            $fundingSourceName = $matches[1];
        }

        $maxLength = 200;
        if (strlen($task->Title) > $maxLength){
            $taskTitle=substr((string)$task->Title,0,$maxLength).'...'.'('.$fundingSourceName.')';
        } else {
            $taskTitle=(string)$task->Title.' ('.$fundingSourceName.')';
        }

        $fundingSource = (string)$task->Project->FundingSource["ID"];

        $taskID = (string)$task["ID"];
        $projectID = (string)$task->Project["ID"];
        $pseudoID = ((int)$projectID * 1024)+ (int)$taskID;
        $tasks[] = array('Title'=>$taskTitle,'ID'=>$pseudoID,'taskID'=>$taskID,'projectID'=>$projectID,'fundSrcID'=>$fundingSource);
    }

    sort($tasks);

    return json_encode($tasks);
}


function getUserID()
{
    global $user;
    if (isset($user->name))
    {
    if (array_key_exists('as_user',$_GET) and isUserAdmin($user->name)) {
          return $_GET['as_user'];
        }
        return $user->name;
    }
    else
    {return null;}
}



?>
