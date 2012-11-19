<?php
include_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';

if (!file_exists('config.php')) {
    echo 'Error: config.php is missing. Please see config.php.example for an example config file.';
    exit;
}
require_once 'config.php';

include_once '/usr/local/share/GRIIDC/php/ldap.php';
include_once '/usr/local/share/GRIIDC/php/drupal.php';

include_once 'lib/functions.php';

include_once 'pdo_functions.php';

$alltasks="";

$conn = pdoDBConnect('pgsql:'.GOMRI_DB_CONN_STRING);

$ldap = connectLDAP('triton.tamucc.edu');
$baseDN = 'dc=griidc,dc=org';
$uid = getDrupalUserName();
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
$tasks = getTasks($ldap,$baseDN,$userDN,$firstName,$lastName);
$GLOBALS['personid'] ="";
if ($_GET) 
{
    if (isset($_GET['dataurl']))
    {
        include 'checkurl.php';
        echo checkURL($_GET['dataurl']);
        
        exit;
    }
    
    if (isset($_GET['uid']))
    {
        $dif_id = $_GET['uid'];
    }
    
    if (isset($_GET['personID'])) 
    {
        $personid = $_GET['personID'];
        ob_clean();
        ob_flush();
        $tasks = filterTasks($tasks,$personid);
        echo displayTaskStatus($tasks,true,$personid);
        exit;
    }
    
    if (isset($_GET['persontask'])) 
    {
        $personid = $_GET['persontask'];
        ob_clean();
        ob_flush();
        $tasks = filterTasks($tasks,$personid);
        echo "<option value=' '>[SELECT A TASK]</option>";
        echo getTaskOptionList($tasks, null);
        exit;
    }
    
    if (isset($_GET['prsid'])) 
    {
        $GLOBALS['personid'] = $_GET['prsid'];
        $alltasks = $tasks;
        $tasks = filterTasks($tasks,$GLOBALS['personid']);
    }
}

if ($_POST)
{
    
    $formHash = sha1(serialize($_POST));
    
    $doi = '';
    
    extract($_POST);
    
    if ($udi == "")
    {
        $query = "SELECT max(registry_id) AS maxregid FROM registry WHERE registry_id like '00.x000.000:%';";
    }
    else
    {
        $query = "SELECT max(registry_id) AS maxregid FROM registry WHERE registry_id like '$udi%';";
    }
    
        
    $result = pdoDBQuery($conn,$query);
    $newserial = (int) substr($result['maxregid'],13,4) + 1;
    $newsub = (int) substr($result['maxregid'],17,3) + 1;
    
    $newserial = str_pad($newserial, 4,'0',STR_PAD_LEFT);
    $newsub = str_pad($newsub, 3,'0',STR_PAD_LEFT);
    
    //echo "newserial:$newserial<br>";
    //echo "newsub:$newsub<br>";
   
    if ($udi == "")
    {
        $reg_id = '00.x000.000:' . $newserial . '.001';
    }
    else
    {
        $reg_id = $udi.'.'.$newsub;    
    }
            
    if ($title == "" OR $abstrct == "" OR $dataurl == "" OR $pocname == "")
    {
        $dMessage = 'Not all required fields where filled out!';
        drupal_set_message($dMessage,'warning');
    }
    else
    {
        //date_default_timezone_set('UTC');
        $now = date('c');
        $ip = $_SERVER['REMOTE_ADDR'];
        
        
        $title = pg_escape_string($title);
        $abstrct = pg_escape_string($abstrct);
        
        //$data_server_type = preg_split('/\:/',$dataurl);
        //$data_server_type = $data_server_type[0];
        
        $query = "INSERT INTO registry 
        (
        registry_id,
        data_server_type,
        dataset_udi,
        dataset_title, 
        dataset_abstract, 
        dataset_poc_name, 
        dataset_poc_email, 
        url_data, 
        url_metadata, 
        username, 
        password, 
        availability_date,
        authentication,
        access_status,
        access_period,
        access_period_start,
        access_period_weekdays,
        data_source_pull,
        doi,
        generatedoi,
        submittimestamp,
        userid
        ) 
        VALUES (
        '$reg_id',
        'HTTP',
        '$udi',
        '$title', 
        '$abstrct', 
        '$pocname', 
        '$pocemail', 
        '$dataurl', 
        '$metadataurl', 
        '$uname', 
        '$pword',
        '$availdate',
        '$auth', 
        '$avail', 
        '$whendl',
        '$dlstart$timezone',
        '$weekdayslst',
        '$pullds', 
        '$doi',
        '$generatedoi',
        '$now',
        '$uid'
        );";
      
        if (!$_SESSION['submitok'])
        {
            $result = pdoDBQuery($conn,$query);
            $dberr = $conn->errorInfo();
            
            if (count($result)==0) 
            {
                $dMessage = "Thank you for your submission. Please email <a href=\"mailto:griidc@gomri.org?subject=DOI Form\">griidc@gomri.org</a> if you have any questions.";
                drupal_set_message($dMessage,'status');
                $_SESSION['submitok'] = true;
            }
            else
            {
                $dMessage= "A database error happened, please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.<br/>".$dberr[2];
                drupal_set_message($dMessage,'error',false);
            }
        }
        else
        {
            $dMessage= "Sorry, the data was already succesfully submitted. Please email <a href=\"mailto:griidc@gomri.org?subject=REG Form\">griidc@gomri.org</a> if you have any questions.";
            drupal_set_message($dMessage,'warning');
            $_SESSION['submitok'] = true;
        }
        
    }
}   
else
{
    $_SESSION['submitok'] = false;
}

if ($_SESSION['submitok'])
{
    include 'submit.php';
}
else
{

    echo '<table  border="0">';
    echo '<tr>';
    echo '<td width="45%" style="vertical-align: top; background: transparent;">';
    include 'reg_form.php';
    echo '</td>';
    echo '<td width="5%">&nbsp;&nbsp;</td>';
    echo '<td width="50%" style="vertical-align: top; background: transparent;">';
    include 'sidebar.php';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
};

?>





