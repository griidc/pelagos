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


function getPersonOptionListByNameTwos($lastName,$firstName, $whom, $ti) {
	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=true';
	$filters = "&taskID=$ti";
	$url = $baseurl.$switch.$filters;
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
	$buildarray=array();
	foreach ($tasks as $task) {
		$peops = $task->xpath('Researchers/Person');
		foreach ($peops as $peoples) {
			$personID = $peoples['ID'];
            $line = "\nselboxs.options[selboxs.options.length] = new \nOption(' $peoples->LastName, $peoples->FirstName - ($peoples->Email)', $personID);";
			array_push($buildarray, $line );

		}
	}
	//$result = array_unique($buildarray);
	$result = $buildarray;
	foreach($result as $ribbit){ echo $ribbit; }
	unset($doc);
}


function getPersonOptionListByNameTwo($lastName,$firstName, $whom, $ti) {
  	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=true';
	#$filters = "&lastName=$lastName&firstname=$firstName";
	$filters = "&taskID=$ti";
	$url = $baseurl.$switch.$filters;
		
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
	$buildarray=array();
	foreach ($tasks as $task) {
		$peops = $task->xpath('Researchers/Person');
		foreach ($peops as $peoples) {
			$personID = $peoples['ID'];
			$line = "\nselbox.options[selbox.options.length] = new \nOption('$peoples->LastName, $peoples->FirstName - ($peoples->Email)', $personID);";
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
	#$filters = "&lastName=$lastName&firstname=$firstName";
	$filters = "&taskID=$ti";
	$url = $baseurl.$switch.$filters;
		
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
	$buildarray=array();
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




function getTaskOptionListByNameTwos($lastName,$firstName, $what) {
	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=false';
	$filters = "&lastName=$lastName&firstname=$firstName";
	$url = $baseurl.$switch.$filters;

	$maxLength = 200;
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');

	foreach ($tasks as $task){
		$dbOptionValue = $task['ID'];
		$dbOption = $taskTitle;
		 echo "if (chosen == \"$dbOptionValue\") { ";
		 getPersonOptionListByNameTwos($lastName,$firstName, 488, $dbOptionValue); 
		 echo" }\n\n";
	}
	unset($doc);
}









function getTaskOptionListByNameTwo($lastName,$firstName, $what) {
	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=false';
	$filters = "&lastName=$lastName&firstname=$firstName";
	$url = $baseurl.$switch.$filters;

	$maxLength = 200;
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');

	foreach ($tasks as $task){
		$dbOptionValue = $task['ID'];
		$dbOption = $taskTitle;
		 echo "if (chosen == \"$dbOptionValue\") { ";
		 getPersonOptionListByNameTwo($lastName,$firstName, 488, $dbOptionValue); 
		 echo" }\n\n";
	}
	unset($doc);
}


function getTaskOptionListByName($lastName,$firstName, $what) {
	global $baseURL;
	$baseurl = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php";
	$switch = '?'.'maxResults=-1&listResearchers=false';
	$filters = "&lastName=$lastName&firstname=$firstName";
	$url = $baseurl.$switch.$filters;

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

