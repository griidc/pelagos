<?php
// @codingStandardsIgnoreFile
date_default_timezone_set('America/Chicago');

if (!file_exists('config.ini')) {
    echo 'Error: config.ini is missing. Please see config.ini.example for an example config file.';
    exit;
}
$drupaluser = $GLOBALS['user'];

$GLOBALS['pelagos_config']  = parse_ini_file('/etc/opt/pelagos.ini',true);

include_once $GLOBALS['pelagos_config']['paths']['share'].'/php/aliasIncludes.php';
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/ldap.php';
include_once $GLOBALS['pelagos_config']['paths']['share'].'/php/drupal.php';
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/dif-registry.php';
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/db-utils.lib.php';
require_once $GLOBALS['pelagos_config']['paths']['share'].'/php/EventHandler.php';
include_once "pdo_functions.php";
include_once "lib/functions.php";

$GLOBALS['ldap_config']     = parse_ini_file($GLOBALS['pelagos_config']['paths']['conf'].'/ldap.ini',true);
$GLOBALS['db_config']       = parse_ini_file($GLOBALS['pelagos_config']['paths']['conf'].'/db.ini',true);
$GLOBALS['module_config']   = parse_ini_file('config.ini',true);
$GLOBALS['logfile_location'] = $GLOBALS['pelagos_config']['paths']['log'];
# log registrations of new metadata to mdapp's log
$mdapp_logfile = "$GLOBALS[logfile_location]/mdapp.log";

define('RPIS_TASK_BASEURL',  $GLOBALS['module_config']['RISDATA']['RPIS_TASK_BASEURL']);
define('RPIS_PEOPLE_BASEURL',$GLOBALS['module_config']['RISDATA']['RPIS_PEOPLE_BASEURL']);

$host   = $GLOBALS['db_config']['GOMRI_RW']['host'];
$user   = $GLOBALS['db_config']['GOMRI_RW']['username'];
$pw     = $GLOBALS['db_config']['GOMRI_RW']['password'];
$port   = $GLOBALS['db_config']['GOMRI_RW']['port'];
$dbname = $GLOBALS['db_config']['GOMRI_RW']['dbname'];

define('GOMRI_DB_CONN_STRING',"host=$host port=$port dbname=$dbname user=$user password=$pw");
$conn = pdoDBConnect('pgsql:'.GOMRI_DB_CONN_STRING);

$isGroupAdmin = false;

$alltasks="";

$registry_fields = array( 'registry_id', 'dataset_udi', 'dataset_title', 'dataset_abstract', 'dataset_originator', 'dataset_poc_name', 'dataset_poc_email', 'access_status', 'doi',
                          'data_server_type', 'url_data', 'availability_date', 'access_period', 'access_period_start', 'access_period_weekdays', 'data_source_pull',
                          'metadata_server_type', 'url_metadata',
                          'userid', 'submittimestamp',
                          'dataset_download_status','metadata_dl_status');

$DBH = OpenDB('GOMRI_RW');

$ldap = connectLDAP($GLOBALS['ldap_config']['ldap']['server']);
$baseDN = $GLOBALS['ldap_config']['ldap']['base_dn'];

$uid = getUID();
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

# get Tasks for RIS Service
$tasks = getTasks($ldap,$baseDN,$userDN,$submittedby);

# if we still have no tasks, show a warning
if (count($tasks) == 0) {
    drupal_set_message("No identified datasets found for $firstName $lastName.<br>If you or someone in your organization has completed a DIF to identify datasets that you are now attempting to register, please contact GRIIDC at <a href='mailto:griidc@gomri.org'>griidc@gomri.org</a>",'warning');
}

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

    if (isset($_GET['regid']))
    {
        $reg_id = $_GET['regid'];
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

$registry_vals = array();

if ($_POST) {

    $formHash = sha1(serialize($_POST));
    if (empty($_POST['title']) or empty($_POST['abstrct']) or empty($_POST['pocemail']) or empty($_POST['pocname']) or empty($_POST['dataset_originator'])) {
        $dMessage = 'Not all required fields where filled out!';
        drupal_set_message($dMessage,'warning');
    }
    else {
        if ($_SESSION['submitok']) {
            $dMessage= "Sorry, the data was already succesfully submitted. Please email <a href=\"mailto:griidc@gomri.org?subject=REG Form\">griidc@gomri.org</a> if you have any questions.";
            drupal_set_message($dMessage,'warning',false);
            $_SESSION['submitok'] = true;
        }
        else {
            foreach ($registry_fields as $field) {
                $registry_vals[$field] = null;
            }

            # determine registry_id
            $SQL = "SELECT MAX(registry_id) AS maxregid FROM registry WHERE registry_id LIKE ?;";
            $sth = $DBH->prepare($SQL);
            if (array_key_exists('dataset_udi',$_POST) and !empty($_POST['dataset_udi'])) {
                $registry_vals['dataset_udi'] = $_POST['dataset_udi'];
                $sth->execute(array("$_POST[dataset_udi].%"));
                $result = $sth->fetch();
                $newsub = (int) substr($result['maxregid'],17,3) + 1;
                $newsub = str_pad($newsub, 3,'0',STR_PAD_LEFT);
                $registry_vals['registry_id'] = $_POST['dataset_udi'].'.'.$newsub;
                if ($newsub > 1) {
                    $sth2 = $DBH->prepare('SELECT data_server_type,metadata_server_type FROM registry WHERE registry_id = ?');
                    $sth2->execute(array($result['maxregid']));
                    $result2 = $sth2->fetch();
                    switch ($_POST['data_server_type']) {
                        case 'upload':
                            if (!array_key_exists('datafile',$_FILES) or empty($_FILES["datafile"]["name"])) {
                                $_POST['data_server_type'] = $result2['data_server_type'];
                            }
                            break;
                        case 'SFTP':
                            if (!array_key_exists('url_data_sftp',$_POST) or empty($_POST['url_data_sftp'])) {
                                $_POST['data_server_type'] = $result2['data_server_type'];
                            }
                            break;
                        case 'HTTP':
                            if (!array_key_exists('url_data_http',$_POST) or empty($_POST['url_data_http'])) {
                                $_POST['data_server_type'] = $result2['data_server_type'];
                            }
                            break;
                    }
                    switch ($_POST['metadata_server_type']) {
                        case 'upload':
                            if (!array_key_exists('metadatafile',$_FILES) or empty($_FILES["metadatafile"]["name"])) {
                                $_POST['metadata_server_type'] = $result2['metadata_server_type'];
                            }
                            break;
                        case 'SFTP':
                            if (!array_key_exists('url_metadata_sftp',$_POST) or empty($_POST['url_metadata_sftp'])) {
                                $_POST['metadata_server_type'] = $result2['metadata_server_type'];
                            }
                            break;
                        case 'HTTP':
                            if (!array_key_exists('url_metadata_http',$_POST) or empty($_POST['url_metadata_http'])) {
                                $_POST['metadata_server_type'] = $result2['metadata_server_type'];
                            }
                            break;
                    }
                }
            }
            else {
                $sth->execute(array('00.x000.000:%'));
                $result = $sth->fetch();
                $newserial = (int) substr($result['maxregid'],13,4) + 1;
                $newserial = str_pad($newserial, 4,'0',STR_PAD_LEFT);
                $registry_vals['registry_id'] = '00.x000.000:' . $newserial . '.001';
            }

            $registry_vals['dataset_title'] = $_POST['title'];
            $registry_vals['dataset_abstract'] = $_POST['abstrct'];
            $registry_vals['dataset_originator'] = $_POST['dataset_originator'];
            $registry_vals['dataset_poc_name'] = $_POST['pocname'];
            $registry_vals['dataset_poc_email'] = $_POST['pocemail'];
            $registry_vals['access_status'] = $_POST['access_status'];
            if (array_key_exists('doi',$_POST) and !empty($_POST['doi'])) {
                $registry_vals['doi'] = $_POST['doi'];
            }
            $registry_vals['data_server_type'] = $_POST['data_server_type'];
            $registry_vals['metadata_server_type'] = $_POST['metadata_server_type'];

            # if we're uploading data, make sure we have a place to put it
            if ($_POST['data_server_type'] == "upload" or $_POST['metadata_server_type'] == "upload") {
                $home_dir = getHomedir($uid);
                if (!is_null($home_dir)) {
                    $home_dir = preg_replace('/\/+$/','',$home_dir);
                    $dest_dir = "$home_dir/incoming";
                }
                if (is_null($home_dir) or !file_exists($dest_dir)) {
                    $dest_dir = "/san/home/upload/$uid/incoming";
                    if (!file_exists("/san/home/upload/$uid")) mkdir("/san/home/upload/$uid");
                    if (!file_exists($dest_dir)) mkdir($dest_dir);
                }
            }

            switch ($_POST['data_server_type']) {
                case 'upload':
                    $data_file_path = null;
                    if (array_key_exists('url_data_upload',$_POST)) $data_file_path = $_POST['url_data_upload'];
                    if (array_key_exists('datafile',$_FILES) and !empty($_FILES["datafile"]["name"])) {
                        if ($_FILES['datafile']['error'] > 0) {
                            echo "Error uploading data file: " . $_FILES['datafile']['error'] . "<br>";
                        }
                        else {
                            move_uploaded_file($_FILES["datafile"]["tmp_name"],"$dest_dir/" . $_FILES["datafile"]["name"]);
                            $data_file_path = "file://$dest_dir/" . $_FILES["datafile"]["name"];
                            $registry_vals['dataset_download_status'] = 'None';
                        }
                    }
                    $registry_vals['url_data'] = $data_file_path;
                    break;
                case 'SFTP':
                    if (array_key_exists('url_data_sftp',$_POST) and !empty($_POST['url_data_sftp'])) {
                        $registry_vals['url_data'] = $_POST['url_data_sftp'];
                        if (array_key_exists('sftp_force_data_download',$_POST) and !empty($_POST['sftp_force_data_download'])) {
                            $registry_vals['dataset_download_status'] = 'None';
                        }
                    }
                    break;
                case 'HTTP':
                    if (array_key_exists('url_data_http',$_POST) and !empty($_POST['url_data_http'])) {
                        $registry_vals['url_data'] = $_POST['url_data_http'];
                        if (array_key_exists('http_force_data_download',$_POST) and !empty($_POST['http_force_data_download'])) {
                            $registry_vals['dataset_download_status'] = 'None';
                        }
                    }
                    if (array_key_exists('availability_date',$_POST) and !empty($_POST['availability_date']))
                        $registry_vals['availability_date'] =  $_POST['availability_date'];
                    if (array_key_exists('access_period',$_POST) and !empty($_POST['access_period'])) {
                        if ($_POST['access_period'] == 'Yes') {
                            $registry_vals['access_period'] = 'true';
                            if (array_key_exists('dlstart',$_POST) and !empty($_POST['dlstart']))
                                $registry_vals['access_period_start'] = "$_POST[dlstart]$_POST[timezone]";
                            if (array_key_exists('access_period_weekdays',$_POST) and !empty($_POST['access_period_weekdays']))
                                $registry_vals['access_period_weekdays'] = $_POST['access_period_weekdays'];
                        }
                        else $registry_vals['access_period'] = 'false';
                    }
                    if (array_key_exists('data_source_pull',$_POST) and !empty($_POST['data_source_pull'])) {
                        if ($_POST['data_source_pull'] == 'Yes') $registry_vals['data_source_pull'] = 'true';
                        else $registry_vals['data_source_pull'] = 'false';
                    }
                    break;
            }

            switch ($_POST['metadata_server_type']) {
                case 'upload':
                    $metadata_file_path = null;
                    if (array_key_exists('upload_metadataurl',$_POST)) {
                        $metadata_file_path = $_POST['upload_metadataurl'];
                    }
                    if (array_key_exists('metadatafile',$_FILES) and !empty($_FILES["metadatafile"]["name"])) {
                        if ($_FILES['metadatafile']['error'] > 0) {
                            echo "Error uploading metadata file: " . $_FILES['metadatafile']['error'] . "<br>";
                        }
                        else {
                            move_uploaded_file($_FILES["metadatafile"]["tmp_name"],"$dest_dir/" . $_FILES["metadatafile"]["name"]);
                            $metadata_file_path = "file://$dest_dir/" . $_FILES["metadatafile"]["name"];
                            $registry_vals['metadata_dl_status'] = 'None';
                            $message = "$drupaluser->name has registered new metadata via direct upload for ".addslashes($_POST['dataset_udi']);
                            writeLog($message,$mdapp_logfile);
                        }
                    }
                    $registry_vals['url_metadata'] = $metadata_file_path;
                    break;
                case 'SFTP':
                    $registry_vals['url_metadata'] = $_POST['url_metadata_sftp'];
                    if (array_key_exists('sftp_force_metadata_download',$_POST) and !empty($_POST['sftp_force_metadata_download'])) {
                        $registry_vals['metadata_dl_status'] = 'None';
                    }
                    $message = "$drupaluser->name has registered new metadata via SFTP/GridFTP upload for ".addslashes($_POST['dataset_udi']);
                    writeLog($message,$mdapp_logfile);
                    break;
                case 'HTTP':
                    if (array_key_exists('url_metadata_http',$_POST) and !empty($_POST['url_metadata_http'])) {

                        // Get the current value of url_metadata from database.
                        $statementHandle = $DBH->prepare('SELECT url_metadata FROM registry WHERE registry_id = ?');
                        $statementHandle->execute(array($_POST['registry_id']));
                        $result = $statementHandle->fetch();
                        $storedUrl = $result['url_metadata'];

                        $registry_vals['url_metadata'] = $_POST['url_metadata_http'];

                        $forceMetadataDownload = false;
                        if (array_key_exists('http_force_metadata_download',$_POST) and !empty($_POST['http_force_metadata_download'])) {
                            $registry_vals['metadata_dl_status'] = 'None';
                            $forceMetadataDownload = true;
                        }

                        // Only make this update if either the Uri to the metadata file is different from what
                        // is on record, or if the 'force' option has been selected by the user.
                        if (($forceMetadataDownload == true) or ($_POST['url_metadata_http'] != $storedUrl)) {
                            $message = "$drupaluser->name has registered new metadata via HTTP pull for ".addslashes($_POST['dataset_udi']);
                            writeLog($message,$mdapp_logfile);
                        }
                    }
            }

            $registry_vals['userid'] = $uid;
            $registry_vals['submittimestamp'] = date('c');

            $SQL = 'INSERT INTO registry (' . join(',',$registry_fields) . ') VALUES (:' . join(',:',$registry_fields) .');';
            $sth = $DBH->prepare($SQL);
            foreach ($registry_fields as $field) {
                $sth->bindValue(":$field",$registry_vals[$field]);
            }
            $result = $sth->execute();

            if ($result) {
                $dMessage = "Thank you for your submission. Please email <a href=\"mailto:griidc@gomri.org?subject=Registration Form\">griidc@gomri.org</a> if you have any questions.";
                drupal_set_message($dMessage,'status',false);

                $registryID = $registry_vals['registry_id'];
                $UDI = $_POST['dataset_udi'];
                $submitAction = $_POST["submit"];

                $userData = array('firstName'=>$firstName,'lastName'=>$lastName);
                $eventData = array('udi'=>$UDI,'userId'=>$uid,'registryID'=>$registryID,'user'=>$userData);

                if ($submitAction == 'Update')
                { $eventAction = 'dataset_registration_updated'; }
                else
                { $eventAction = 'dataset_registration_submitted'; }

                eventHappened($eventAction,$eventData);

                $_SESSION['submitok'] = true;
            }
            else {
                $errorInfo = $sth->errorInfo();
                $errorDetails = "URI: $_SERVER[REQUEST_URI]\nDatabase Error Code: $errorInfo[0]\nStatement Error Code: $errorInfo[1]\nStatement Error Message: $errorInfo[2]";
                $dMessage= "A database error has occurred, please contact GRIIDC at <a href=\"mailto:griidc@gomri.org?subject=Dataset Registration Error&body=".rawurlencode($errorDetails)."\">griidc@gomri.org</a> and provide the following information:<pre>$errorDetails</pre>";
                drupal_set_message($dMessage,'error',false);
            }

        }

    }

}
else {
    $_SESSION['submitok'] = false;
}

if ($_SESSION['submitok']) {
    include 'submit.php';
    # trigger filer
    system($GLOBALS['pelagos_config']['paths']['root'].'/back-end/filer/trigger-filer');
}
else {
    echo '<script>';
    echo 'jQuery(document).ready(function() { jQuery("#pelagos-content > table > tbody > tr > td:last-child").height(jQuery("#pelagos-content > table > tbody > tr > td:first-child").height()); });';
    echo '</script>';
    echo '<table border="0" style="height:100%;width:100%;">';
    echo '<tr>';
    echo '<td width="60%" style="vertical-align: top; background: transparent;">';
    include 'reg_form.php';
    echo '</td>';
    echo '<td width="*">&nbsp;&nbsp;</td>';
    echo '<td width="40%" style="vertical-align: top; background: transparent;">';
    include 'sidebar.php';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
};

function writeLog($message,$logfile) {
    $dstamp = date('r'); // RFC 2822 standard
    file_put_contents($logfile,"$dstamp:$message\n", FILE_APPEND);
}


?>
