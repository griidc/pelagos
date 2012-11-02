<?php

// Module: dif.php
// Author(s): Jew-Lee Irena Lann
// Last Updated: 30 October 2012
// Parameters: Form fields with to add to the database or update.
// Returns: Form / Sidebar
// Purpose: Wrapper for form and action scripts to update database & email at later date.

error_reporting(0);   
include_once '/usr/local/share/GRIIDC/php/ldap.php';
include_once '/usr/local/share/GRIIDC/php/drupal.php';
include ('functions.php'); 
include ("dbGomri.php");
include ("config.php");
$ldap = connectLDAP('triton.tamucc.edu');
$baseDN = 'dc=griidc,dc=org';
$uid = getDrupalUserName();
if (isset($uid)) {
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

if ($_GET) 
{
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
    else
    {
        unset($GLOBALS['personid']);
    }
}

 ?> 

<html> 
<head> 
  <title></title>
  <LINK href="/dif/includes/css/overwrite.css" rel="stylesheet" type="text/css">
  <LINK href="/dif/includes/css/Tooltip.css" rel="stylesheet" type="text/css">
  <!--<SCRIPT LANGUAGE="JavaScript" SRC="/dif/includes/js/ds.js"> </SCRIPT>-->
  <script language="javascript" src="/dif/includes/js/jquery-1.2.6.min.js"></script>
 <script src="/dif/includes/js/Tooltip.js"></script> 
  <script src="/dif/includes/js/jquery-latest.js"></script>
  <script type="text/javascript" src="/dif/includes/js/jquery.validate.js"></script>
  <div class="bgCover">&nbsp;</div>
  <div class="overlayBox">
	<div class="overlayContent">
        <a href="#" class="closeLink">X</a>
        <h2><IMG SRC="images/info.png"> INFO</h2>
        <p></p>
	</div>
   </div>

<link rel="StyleSheet" href="/dif/includes/css/dtree.css" type="text/css" />
<script type="text/javascript" src="/dif/includes/js/dtree.js"></script>
<script type="text/javascript">
function updateTaskList(personID) {
   if (window.XMLHttpRequest) { xmlhttp=new XMLHttpRequest(); } else { xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); }
       xmlhttp.onreadystatechange=function updateTaskList() {
            if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                var here = document.getElementById("ctask").innerHTML=xmlhttp.responseText;
                var help=document.getElementById('span1').innerHTML = " <select id='ctask' name='task' style='width:800px;' size='1' onchange='setOptions(document.ed.task.options[document.ed.task.selectedIndex].value);' class='required' >" + here + "</select>";
            }
        }
        xmlhttp.open("GET","?persontask="+personID,true);
        xmlhttp.send();
    }
</script>

<script type="text/javascript"> 

function stopRKey(evt) { 
  var evt = (evt) ? evt : ((event) ? event : null); 
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null); 
  if ((evt.keyCode == 13) && (node.type=="text"))  {return false;} 
} 
document.onkeypress = stopRKey; 
</script> 
   
   
   <script language="JavaScript">
<!--
function enable_text(status)
{
status=!status;
document.ed.video.disabled = status;
document.ed.video.value = "";
}
//-->
</script>
   <script type="text/javascript">
    function getVal(){
    var el=document.getElementById('inp0');
    var i=0, c, arr=[];
    while(c=document.getElementById('chk'+(i++))) c.checked? arr[arr.length]=c.value : null;
     el.value = arr.join(";");
    }
   </script>
   <script language="javascript" type="text/javascript">
    function imposeMaxLength(Object, MaxLen)
    {
      return (Object.value.length <= MaxLen);
    }
   </script>
   <style type="text/css">
     p { clear: both; }
     .submit { margin-left: 12em; }
     em { font-weight: bold; padding-right: 1em; vertical-align: top; color:#FF0000;}
   </style>
  <script>
     $(document).ready(function(){
     $("#commentForm").validate();
     });
  </script>
<!--<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<SCRIPT LANGUAGE=JAVASCRIPT SRC="/dif/includes/js/map.js"></SCRIPT>
<LINK href="/dif/includes/css/map.css" rel="stylesheet" type="text/css">-->

   	<script language="javascript" type="text/javascript">
      function setOptions(chosen) {
	     var selboxp = document.ed.ppoc;
         var selboxs = document.ed.spoc;
		 selboxp.options.length = 0;
	     if (chosen == "") { selboxp.options[selboxp.options.length] = new Option('Please Choose a Task First',''); }
	     else{ 
		 <?php makeTaskGrouping($tasks, "p"); ?>  }
	     selboxs.options.length = 0;
         if (chosen == "") { selboxs.options[selboxs.options.length] = new Option('Please Choose a Task First',''); }
		 else{  <?php makeTaskGrouping($tasks, "s");  ?>  }
       }
</script>


</head>

<body <?PHP  if ($_GET['uid']==""){echo "onload=enable_text(false);";} ?>>

<?php 
if (!$submittedby){$submittedby = '-1';}
//CONNECTION TO POSTGRES
$connection = pg_connect("host=$dbserver port=$port dbname=$database user=$username password=$password") or die ("ERROR: " . pg_last_error($connection)); 
if (!$connection) { die("Error in connection: " . pg_last_error()); } 
$pu=array();
$result3 = pg_exec($connection, "SELECT var_name, comments FROM form_info ORDER BY form_info.id ASC");
if (!$result3) { die("Error in SQL query: " . pg_last_error()); } 
while($row = pg_fetch_row($result3)){
echo " <div id=\"$row[0]_tip\" style=\"display:none;\"> <img src=\"/dif/images/info.png\" style=\"float:right;\" /> $row[1]</div>";
array_push($pu, $row[0]); }
//SUBMITTED
$status = 0;$usernumber=1;
if (($_POST['submit'])||($_POST['later'])||($_POST['reject'])||($_POST['accept'])) { 
if ($_POST['later']) { $status = 0;}else{$status = 1;}
   //CONCAT TO FIT DB
   foreach ($_POST as $k=>$v) { 
//if (!$v){$v="NULL";}
//echo "$k and $v<HR>";
//array_push($ssa, "$k|$v"); 

$$k = pg_escape_string($v);}

   list($task, $project)=explode("|", $task);
   $title = str_replace(array("\r\n", "\n\r", "\r", "\n", "\t"), " ", $title);
   $datafor = $eco.'|'.$phys.'|'.$atm.'|'.$ch.'|'.$geog.'|'.$scpe.'|'.$econom.'|'.$geop.'|'.$dtother;
   $approach = $field."|".$sim."|".$lab."|".$lit."|".$remote."|".$approachother;
   $sdate="$sye-$smo-01";$edate="$eye-$emo-01";
   $standards=$s1."|".$s2."|".$s3."|".$s4."|".$otherst;
   $point =$a1."|".$a2."|".$a3."|".$accessother;
   $privacy = $privacy."|".$privacyother;
   $national= $nat1."|".$nat2."|".$nat3."|".$nat4."|".$nat5."|".$nat6."|".$othernat;
   $datatype =$sascii."|".$uascii."|".$images."|".$netCDF."|".$dtvideo."|".$video."|".$gml."|".$otherdty;
//SQL
if ($_POST['reject']) { $status = 0;}
if ($_POST['accept']) { $status = 2;}
if ($_POST['accept'] OR $_POST['reject'])
{ 
	$uid =$modts;
	$sql = "UPDATE datasets SET status='".$status ."'  WHERE dataset_uid='".$uid."'";
}
else
{




if ($flag== "update"){$uid =$modts;
    $sql = "UPDATE datasets SET dataset_uid='".$uid."', task_uid='".$task."', title='".$title."', abstract='".$abstract."', dataset_type='".$datatype."', dataset_for='".$datafor."', size='".$size."', observation='".$observation ."', approach='".$approach ."', historic_links='".$historical."', meta_editor='".$ed ."', meta_standards='".$standards."', point='".$point."', national='".$national ."', ethical='".$privacy."', remarks='".$remarks ."', primary_poc=";
    if ($ppoc ==""){$sql .="null";}else{ $sql.="'".$ppoc."'";}
    $sql .=", secondary_poc=";
    if ($spoc ==""){$sql .="null";}else{ $sql.="'".$spoc."'";}
    $sql.=", logname='".$submittedby."', status='".$status."', project_id=";
    if ($project ==""){$sql .="null";}else{ $sql.="'".$project."'";}
    $sql.=", start_date='".$sdate."', end_date='".$edate."', geo_location='".$geoloc ."'  WHERE dataset_uid='".$uid."'";
}else{
    $uid = time();
    $sql = "INSERT INTO datasets(dataset_uid, task_uid, title, abstract, dataset_type, dataset_for, size, observation, approach, start_date, end_date, geo_location, historic_links, meta_editor, meta_standards, point, national, ethical, remarks, primary_poc, secondary_poc, logname, status, project_id) VALUES('$uid', '$task', '$title', '$abstract', '$datatype', '$datafor', '$size', '$observation', '$approach', '$sdate', '$edate','$geoloc', '$historical', '$ed', '$standards', '$point', '$national', '$privacy', '$remarks', ";
if ($ppoc ==""){$sql .="null";}else{ $sql.="'".$ppoc."'";}
   $sql.=", ";
   if ($spoc ==""){$sql .="null";}else{ $sql.="'".$spoc."'";}
   $sql .=", '$submittedby','$status', ";
   if ($project ==""){$sql .="null";}else{ $sql.="'".$project."'";}
   $sql .=")";
}
}
$result = pg_query($connection, $sql); 
if (!$result) { $mymesg="error"; $yn= "Something Went Wrong!!!" . pg_last_error(); } else { $mymesg= "status"; $yn= "Data successfully inserted"; } 
echo " <div class=\"messages ". $mymesg."\"> <h2 class=\"element-invisible\">". $mymesg."message</h2> <ul> <li>$yn</li> </ul> </div> <br /> "; 
pg_free_result($result); 

$status=0;
$flag="";
//Take Input from sidebar.
}elseif ($uid=$_GET['uid']){
  $sql5 = "SELECT * FROM datasets where dataset_uid=".$uid;
  $result4 = pg_exec($connection, $sql5);
  if (!$result4) { die("Error in SQL query: " . pg_last_error()); } 
  $m = pg_fetch_row($result4);
    //EXPLODE FOR DATA POPULATION 
    $status=$m[22];
    list($stand[0], $stand[1], $stand[2], $stand[3], $stand[4])=explode("|", $m[14] ); 
    list($point[0], $point[1], $point[2], $point[3])=explode("|", $m[15] );
    list($zz[0], $zz[1])=explode("|", $m[17]);
    list($aq[0], $aq[1], $aq[2], $aq[3], $aq[4], $aq[5])=explode("|", $m[8] ); 
    list($dtt[0], $dtt[1], $dtt[2], $dtt[3], $dtt[4], $dtt[5], $dtt[6], $dtt[7]) = explode("|", $m[4]); 
    list($l[0], $l[1],  $junk2)=explode("-", $m[9]);
    list($n[0], $n[1], $junk)=explode("-", $m[10]);
    list($dtf[0], $dtf[1], $dtf[2], $dtf[3], $dtf[4], $dtf[5], $dtf[6], $dtf[7],  $dtf[8]) = explode("|", $m[5]); 
    foreach ($m as $kk=>$vv) {  $$kk = pg_escape_string($vv); }
$mtask = $m[1]."|".$m[24];

    $flag="update";
}
//CLOSE CONNECTIONS AND FREE RESOURCES
pg_close($connection); 
//FORM
include("dataset_form.php");

?> 
</body> 
</html> 
