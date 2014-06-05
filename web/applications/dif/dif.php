<?php

include 'difDL.php'; //dif DataLayer

include_once '/home/users/mvandeneijnden/public_html/quartz/php/ldap.php'; 

//include_once 'getHelpText.php';

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
            $difUID = $_POST["udi"];
            header('Content-Type: application/json');
            echo getFormData($difUID);
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

function postDIF($fielddata)
{
    foreach ($fielddata as $field)
    {
        usleep(25000);
    }
    
    //return json_encode(array('success'=>false,'message'=>'<img src="includes/images/cancel.png"><p>There was an error! Form NOT submitted.</p>'));
    
    return json_encode(array('success'=>true,'message'=>'<div><img src="includes/images/info32.png"><p>The form was submitted succesfully</p></div>'));
    
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
    
    $data = $data[0];

    $formArr = array();
    
    $formArr = array_merge($formArr,array("udi"=>$data["UDI"]));
    $PseudoTaskNumber = ((int)$data["projectid"] * 1024) + ((int)$data["taskid"] );
    $formArr = array_merge($formArr,array("task"=>$PseudoTaskNumber));
    $formArr = array_merge($formArr,array("title"=>$data["Title"]));
    
    $formArr = array_merge($formArr,array("primarypoc"=>$data["POC"]));
    $formArr = array_merge($formArr,array("secondarypoc"=>$data["sPOC"]));
    $formArr = array_merge($formArr,array("abstract"=>$data["Abstract"]));
    $formArr = array_merge($formArr,array("size"=>$data["size"]));
    $formArr = array_merge($formArr,array("observation"=>$data["observation"]));
    $formArr = array_merge($formArr,array("historical"=>$data["historic_links"]));
    $formArr = array_merge($formArr,array("remarks"=>$data["Remarks"]));
    $formArr = array_merge($formArr,array("status"=>$data["Access_Status"]));
    
    $dataSetType = preg_split ("/\|/",$data["dataset_type"]);

    $formArr = array_merge($formArr,array("dtascii"=>($dataSetType[0]=='Structured, Generic Text/ASCII File (CSV, TSV)')));
    $formArr = array_merge($formArr,array("dtuascii"=>($dataSetType[1]=='Unstructured, Generic Text/ASCII File (TXT)')));
    $formArr = array_merge($formArr,array("dtimages"=>($dataSetType[2]=='Images')));
    $formArr = array_merge($formArr,array("dtnetcdf"=>($dataSetType[3]=='CDF/netCDF')));
    $formArr = array_merge($formArr,array("dtvideo"=>($dataSetType[4]=='Video')));
    $formArr = array_merge($formArr,array("dtgml"=>($dataSetType[6]=='GML/XML Structured')));
    $formArr = array_merge($formArr,array("dtvideoatt"=>$dataSetType[5]));
    $formArr = array_merge($formArr,array("dtother"=>$dataSetType[7]));
    
    //$dataSetFor = preg_split ("/\|/",$data["dataset_for"]);
    
    $formArr = array_merge($formArr,array("dfeco"=>$data["Class_Ecological"]));
    $formArr = array_merge($formArr,array("dfphys"=>$data["Class_PhysOceanography"]));
    $formArr = array_merge($formArr,array("dfatm"=>$data["Class_Athmospheric"]));
    $formArr = array_merge($formArr,array("dfchem"=>$data["Class_Chemical"]));
    $formArr = array_merge($formArr,array("dfhumn"=>$data["Class_Health"]));
    $formArr = array_merge($formArr,array("dfscpe"=>$data["Class_Social"]));
    $formArr = array_merge($formArr,array("dfeconom"=>$data["Class_Economic"]));
    $formArr = array_merge($formArr,array("dfother"=>$data["Class_Other"]));
    
    //$dataSetProc = preg_split ("/\|/",$data["approach"]);
    
    $formArr = array_merge($formArr,array("appField"=>$data["Acq_FieldSample"]));
    $formArr = array_merge($formArr,array("appSim"=>$data["Acq_Simulated"]));
    $formArr = array_merge($formArr,array("appLab"=>$data["Acq_Lab"]));
    $formArr = array_merge($formArr,array("appLit"=>$data["Acq_Literature"]));
    $formArr = array_merge($formArr,array("appRemote"=>$data["Acq_RemoteSense"]));
    $formArr = array_merge($formArr,array("appOther"=>$data["Acq_Other"]));
    
    $formArr = array_merge($formArr,array("startdate"=>$data["Project_Start"])); 
    $formArr = array_merge($formArr,array("enddate"=>$data["Project_End"])); 
    
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
    
    //$dataCenter = preg_split ("/\|/",$data["national"]);
    
    $formArr = array_merge($formArr,array("repoNodc"=>$data["Facility_NODC"]));
    $formArr = array_merge($formArr,array("repoUsepa"=>$data["Facility_EPAStoret"]));
    $formArr = array_merge($formArr,array("repoGbif"=>$data["Facility_GBIF"]));
    $formArr = array_merge($formArr,array("repoNcbi"=>$data["Facility_NCBI"]));
    $formArr = array_merge($formArr,array("repoDatagov"=>$data["Facility_DMS"]));
    $formArr = array_merge($formArr,array("repoGriidc"=>$data["Facility_GRIIDC"]));
    $formArr = array_merge($formArr,array("repoOther"=>$data["Facility_Other"]));
    
    $ethical = preg_split ("/\|/",$data["ethical"]);
    
    $formArr = array_merge($formArr,array("privacy"=>$ethical[0]));
    $formArr = array_merge($formArr,array("privacyother"=>$ethical[1]));
    
    //$formArr = array_merge($formArr,array("isadmin"=>$isadmin));
    
    return json_encode($formArr);
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
                $listArray[] = array("text"=>$taskTitle,"icon"=>"/dif/images/nofolder.png","li_attr"=>array("title"=>$taskTitle));       
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
    
    // echo "isadmin:$isadmin<br>";
    // echo "isDManager:$isDManager<br>";
    // echo "isDIFApprover:$isDIFApprover<br>";
    
    $twigdata = array('isadmin'=>$isadmin,'isdmanager'=>$isDManager,'isdifapprover'=>$isDIFApprover);
   
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