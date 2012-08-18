<?PHP 
error_reporting(0);
global $user;
	$userId = $user->name;
	$ldap = ldap_connect("ldap://triton.tamucc.edu");
        #$ldap = ldap_connect("ldap://proteus.tamucc.edu");
	$result = ldap_search($ldap, "ou=people,dc=griidc,dc=org", "(uid=$userId)", array('givenName','sn'));
	$entries = ldap_get_entries($ldap, $result);
	for ($i=0; $i<$entries['count']; $i++) 
	{
		$firstName = $entries[$i]['givenname'][0];
		$lastName = $entries[$i]['sn'][0];
		
	}
#####################################################
#          IF ADMIN - SUB FOR A SHORTER USER ID     #
#####################################################
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
        <a href="#" class="closeLink">XX</a>
        <h2><IMG SRC="images/info.png"> INFO</h2>
        <p></p>
	</div>
   </div>
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
</head>

<body >
<?php 
####################################################################
#                   CONNECTION TO POSTGRES                        #
####################################################################
$connection = pg_connect("host=localhost port=5432 dbname=gomri user=gomri_user password=Sharkbait!")  
or die ("ERROR: " . pg_last_error($connection)); 
if (!$connection) { die("Error in connection: " . pg_last_error()); } 
####################################################################
#                    Check ID                        #
####################################################################
$pu=array();
$result3 = pg_exec($connection, "SELECT * FROM form_info ORDER BY form_info.id ASC");
if (!$result3) { die("Error in SQL query: " . pg_last_error()); } 
while($row = pg_fetch_row($result3)){
echo " <div id=\"demo$row[0]_tip\" style=\"display:none;\"> <img src=\"/dif/images/info.png\" style=\"float:right;\" /> $row[1]</div>";
array_push($pu, $row[0]); }
####################################################################
#                      SUBMITTED!!!!                               #
####################################################################
$status = 0;$usernumber=1;
if (($_POST['submit'])||($_POST['later'])) { 
if ($_POST['later']) { $status = 0;}else{$status = 1;}
   #################################################################
   #                  CONCAT TO FIT DB                             #
   #################################################################
   foreach ($_POST as $k=>$v) { $$k = pg_escape_string($v); }
   $datafor = $eco.'|'.$phys.'|'.$atm.'|'.$ch.'|'.$geog.'|'.$scpe.'|'.$econom.'|'.$geop.'|'.$dtother;
   $approach = $field."|".$sim."|".$lab."|".$lit."|".$remote."|".$approachother;
   $sdate="$sye-$smo-01";$edate="$eye-$emo-01";
   $standards=$s1."|".$s2."|".$s3."|".$s4."|".$otherst;
   $point =$a1."|".$a2."|".$a3."|".$accessother;
   $privacy = $privacy."|".$privacyother;
   $national= $nat1."|".$nat2."|".$nat3."|".$nat4."|".$nat5."|".$othernat;
   $datatype =$sascii."|".$uascii."|".$images."|".$netCDF."|".$dtvideo."|".$video."|".$gml."|".$otherdty;
####################################################################
#                               SQL                                #
####################################################################
if ($flag== "update"){$uid =$modts;
$sql = "UPDATE datasets SET dataset_uid='".$uid."', task_uid='".$task."', title='".$title."', abstract='".$abstract."', dataset_type='".$datatype."', dataset_for='".$datafor."', size='".$size."', observation='".$observation ."', approach='".$approach ."', historic_links='".$historical."', meta_editor='".$ed ."', meta_standards='".$standards."', point='".$point."', national='".$national ."', ethical='".$privacy."', remarks='".$remarks ."', primary_poc='".$ppoc."', secondary_poc='".$spoc ."', logname='".$usernumber."', status='".$status ."', start_date='".$sdate."', end_date='".$edate."', geo_location='".$geoloc ."'  WHERE dataset_uid='".$uid."'";
}else{
$uid = time();
$sql = "INSERT INTO datasets(dataset_uid, task_uid, title, abstract, dataset_type, dataset_for, size, observation, approach, start_date, end_date, geo_location, historic_links, meta_editor, meta_standards, point, national, ethical, remarks, primary_poc, secondary_poc, logname, status) VALUES('$uid', '$task', '$title', '$abstract', '$datatype', '$datafor', '$size', '$observation', '$approach', '$sdate', '$edate','$geoloc', '$historical', '$ed', '$standards', '$point', '$national', '$privacy', '$remarks', '$ppoc', '$spoc', '1','$status')";
}
$result = pg_query($connection, $sql); 
if (!$result) { $mymesg="error"; $yn= "Something Went Wrong!!!" . pg_last_error(); } else { $mymesg= "status"; $yn= "Data successfully inserted"; } 
echo " <div class=\"messages ". $mymesg."\"> <h2 class=\"element-invisible\">". $mymesg."message</h2> <ul> <li>$yn</li> </ul> </div> <br /> "; 
pg_free_result($result); 
#####################################################################
#mail("griidc.info@gomri.org", "[New dataset]-Submitted by $firstName $lastName", "".$title $q.\n\n REVIEW: \n".$sql."\n\nHeader Table:\n".$sql2."\n", "From: griidc.infor@gomri.org\n");
#$status=0;
#$flag="";
#####################################################################
##               PICK ONE FROM THE SIDE BAR                         #
#####################################################################
}elseif ($uid=$_GET['uid']){
  $sql5 = "SELECT * FROM datasets where dataset_uid=".$uid;
  $result4 = pg_exec($connection, $sql5);
  if (!$result4) { die("Error in SQL query: " . pg_last_error()); } 
  $m = pg_fetch_row($result4);
    #################################################################
    #               EXPLODE 4 DATA POPULATION                       #
    #################################################################
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
    $flag="update";
}
#####################################################################
#            CLOSE CONNECTIONS AND FREE RESOURCES                  #
#####################################################################
pg_close($connection); 
#####################################################################
#                               FORM                               #
#####################################################################
include("dataset_form.php");
?> 
</body> 
</html> 
