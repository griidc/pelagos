<?PHP
// Module: functions.php
// Author(s): Jew-Lee I. Lann, Michael van den Eijnden
// Last Updated: 21 August 2012
// Parameters: None
// Returns: functions
// Purpose: Has several functions for DIF.

$change=array("01"=>"Jan.","02"=>"Feb.","03"=>"Mar.","04"=>"Apr.","05"=>"May ","06"=>"Jun.","07"=>"Jul.","08"=>"Aug.","09"=>"Sep.","10"=>"Oct.","11"=>"Nov.","12"=>"Dec.");
$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";

function test_print($item2, $key,  $prefix) {
		echo "<option value=".$key;  
	if ($prefix==$key){echo " SELECTED";}  echo ">".$item2."</option>\n";
	}

function isAdmin() {
    global $user;
    $admin = false;
    if ($user->uid) {
        $logged_in_uid = $user->name;
        $ldap = ldap_connect('ldap://triton.tamucc.edu');
        $adminsResult = ldap_search($ldap, "cn=administrators,ou=DIF,ou=applications,dc=griidc,dc=org", '(objectClass=*)', array("member"));
        $admins = ldap_get_entries($ldap, $adminsResult);
        for ($i=0;$i<$admins[0]['member']['count'];$i++) {
            if ("uid=$logged_in_uid,ou=members,ou=people,dc=griidc,dc=org" == $admins[0]['member'][$i]) {
                $admin = true;
            }
        }
    }
    return $admin;
}

function makeTaskGrouping($lastName,$firstName, $which) {
	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=false';
	$filters = "&lastName=$lastName&firstname=$firstName";
	$url = $baseurl.$switch;
    if (!isAdmin()) $url .= $filters;
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
	foreach ($tasks as $task){
		$dbOptionValue = $task['ID'];
		$dbOption = $taskTitle;
		 echo "if (chosen == \"$dbOptionValue\") { ";
		 callPeople($which, $dbOptionValue);
		 echo" }\n\n";
	}
	unset($doc);
}
		
	function callPeople($w, $ti) {
  	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=true';
	$filters = "&taskID=$ti";
	$url = $baseurl.$switch.$filters;		
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
	$he = "\nselboxs.options[selboxs.options.length] = new \nOption('[SELECT]', '999');";
	if ($w=="s"){$buildarray=array($he);}else{$buildarray =array();}
	foreach ($tasks as $task) {
		$peops = $task->xpath('Researchers/Person');
		foreach ($peops as $peoples) {
			$personID = $peoples['ID'];
			#if ($personID== "514"){$bool = 1;}else{$bool = 0;}
			$bool = 0;
			$LastName = preg_replace('/\'/','\\\'',$peoples->LastName);
			$FirstName = preg_replace('/\'/','\\\'',$peoples->FirstName);
			$Email = preg_replace('/\'/','\\\'',$peoples->Email);
			$line = "\nselbox$w.options[selbox$w.options.length] = new \nOption('$LastName, $FirstName - ($Email)', $personID, '', $bool);";
			array_push($buildarray, $line );
		}
	}
	$result = array_unique($buildarray);
	foreach($result as $ribbit){ echo $ribbit; }
	unset($doc);
}
   	
function getPersonOptionListByName($lastName,$firstName, $whom, $ti) {
  	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=true';
	$filters = "&lastName=$lastName&firstname=$firstName&taskID=$ti";
	//$filters = "&taskID=$ti";
	$url = $baseurl.$switch;
    if (!isAdmin()) $url .= $filters;
		
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
	$buildarray=array('<option value=" " selected="selected">[SELECT]</option>');
	foreach ($tasks as $task) {
		$peops = $task->xpath('Researchers/Person');
		foreach ($peops as $peoples) {
			$personID = $peoples['ID'];
			$line= "<option value=\"$personID\"";
			if ($whom==$personID){$line .= " SELECTED";}
			$line.= ">$peoples->LastName, $peoples->FirstName ($peoples->Email)</option>";
			array_push($buildarray, $line );

		}
	}
	$result = array_unique($buildarray);
	foreach($result as $ribbit){ echo $ribbit; }
	unset($doc);
}

function getTaskOptionListByName($lastName,$firstName, $what) {
	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=false';
	$filters = "&lastName=$lastName&firstname=$firstName";
	$url = $baseurl.$switch;
    if (!isAdmin()) $url .= $filters;

	$maxLength = 200;
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');

	foreach ($tasks as $task){
		if (strlen($task->Title) > $maxLength){
			$taskTitle=substr($task->Title,0,$maxLength).'...';
		} else {
			$taskTitle=$task->Title;
		}
		$dbOptionValue = $task['ID'];
		$dbOption = $taskTitle;
		echo "<option value=\"$dbOptionValue\"";
		if ($what==$dbOptionValue){echo " SELECTED";}
		echo ">$dbOption</option>";
	}
	unset($doc);
}

function getProjectOptionListByName($lastName,$firstName) {
	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=false'; 
	$filters = "&lastName=$lastName&firstname=$firstName";
	$url = $baseurl.$switch.$filters;
	
	$maxLength = 50;
	$doc = simplexml_load_file($url);
	$projectList = array();
	$tasks = $doc->xpath('Task');
	foreach ($tasks as $task){
		$projectName = $task->Project->Title;
		array_push($projectList, $projectName);
	}
	$projectList = array_unique($projectList);

	foreach ($projectList as $title) {
		if (strlen($title) > $maxLength){
			$projectTitle=substr($title,0,$maxLength).'...';
		} else {
		$projectTitle = $title;
		}
		echo "<option value=\"$title\">$projectTitle</option>";
	}
	unset($doc);
}
?>

