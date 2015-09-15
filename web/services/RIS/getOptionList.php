<?php
// @codingStandardsIgnoreFile
#$firstName = "Vernon";
#$lastName="Asper";
$baseURL = 'https://proteus.tamucc.edu/~mvandeneijnden/ProjectDB/getTaskDetails.php?';
#################################################################################################
#                               GET PEOPLE DETAILS FUNCTION  //JIL 20120808                     #
#################################################################################################
function getPersonOptionListByName($lastName,$firstName, $whom)
{
  global $baseURL;
  $url = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php?maxResults=-1&listResearchers=true&lastName=$lastName&firstname=$firstName";
  $maxLength = 98; 
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
//sort($result);
foreach($result as $ribbit){ 









echo $ribbit; }
unset($doc);
}




function getTaskOptionListByName($lastName,$firstName, $what)
{
	global $baseURL;
	$url = "http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php?maxResults=-1&listResearchers=false&lastName=$lastName&firstname=$firstName";
	$maxLength = 200;
	$doc = simplexml_load_file($url);
        $tasks = $doc->xpath('Task');
	foreach ($tasks as $task)
	{
		if (strlen($task->Title) > $maxLength) 
		{
			$taskTitle=substr($task->Title,0,$maxLength).'...';
		}
		else
		{
			$taskTitle=$task->Title;
		}
		$dbOptionValue = $task['ID']; //->Title;
		$dbOption = $taskTitle;

		echo "<option value=\"$dbOptionValue\"";


if ($what==$dbOptionValue){echo " SELECTED";} 
echo ">$dbOption</option>";

	}
	
	unset($doc);
}








function getProjectOptionListByName($lastName,$firstName)
{
	global $baseURL;
	$url = $baseURL . "maxResults=-1&listResearchers=false&lastName=$projectLastName&firstname=$projectFirstName";
	
	$maxLength = 50;
	
	$doc = simplexml_load_file($url);
	
	$projectList = array();
	$tasks = $doc->xpath('Task');
	foreach ($tasks as $task)
	{
		$projectName = $task->Project->Title;
		array_push($projectList, $projectName);
	}
	
	$projectList = array_unique($projectList);
	
	//echo var_dump($projectList);
	//exit;
	
	foreach ($projectList as $title)
	{
		if (strlen($title) > $maxLength) 
		{
			$projectTitle=substr($title,0,$maxLength).'...';
		}
		else
		{
			$projectTitle = $title;
		}
		echo "<option value=\"$title\">$projectTitle</option>";
		
	}
	
	unset($doc);
}



?>
