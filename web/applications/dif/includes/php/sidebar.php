<?php
// Module: sidebar.php
// Author(s): Michael van den Eijnden
// Last Updated: 21 August 2012
// Parameters: None
// Returns: Folder list op datasets by task.
// Purpose: To return data gather from the RPIS service and database to show a list of datasets by task.
?>

<LINK href="/dif/includes/css/sidebar.css" rel="stylesheet" type="text/css">
<?php
echo "<table class=cleair><tbody class=tbody><tr><td>";
echo " <h2 class=\"title\" align=center>Tasks and datasets for ".$firstName." ".$lastName."<hr /></FONT>";
echo "</h2></td></tr><tr><td><div style=width:100%;height:800px;overflow:auto; BGCOLOR=#efefef>";
	displayTaskStatusByName($lastName,$firstName);
echo "</div></td></tr> </tbody> </table>";
function displayTaskStatusByName($lastName, $firstName)
{
	$baseurl = 'http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php';
	$switch = '?'.'maxResults=-1'; 				
	$filters = "&lastName=$lastName&firstName=$firstName"; 
	$url = $baseurl . $switch . $filters;

	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
        echo '<ol class=" tree">';
	foreach ($tasks as $task)
	{
		$taskID = $task['ID'];
		
		$taskTitle = $task->Title;
		echo '<li class="folder"><label for="'.$taskID.'">'.$taskTitle.'</label> <input type="checkbox" checked id="'.$taskID.'"/>';
		$query = "select title,status,dataset_uid from datasets where task_uid=$taskID";
		$results = dbexecute($query);
		echo '<ol>';
		while ($row = pg_fetch_row($results)) 
		{
			$status = $row[1];
			$title = $row[0];
			$datasetid = $row[2];
									
			switch ($status)
			{
				case null:
				echo '<li class="redfile">';
				break;
				case 0:
				echo '<li class="redfile">';
				break;
				case 1:
				echo '<li class="yellowfile">';
				break;
				case 2:
				echo '<li class="greenfile">';
				break;
			}
		echo '<a href="/dif?uid='.$datasetid.'">'.$title.'</a></li>';
		}
		echo '</ol></li>';
	}
	echo '</ol>';
	
}

function dbconnect()
{
	$username='gomri_user';
	$password='Sharkbait!';
	$database='gomri';
	$dbserver='localhost';
	$port=5432;
	
	//Connect to database
	$connString = "host=$dbserver port=$port dbname=$database user=$username password=$password";
	$dbconn = pg_connect($connString)
	or die("Couldn't Connect " . pg_last_error());
	
	//Check it
	if(!($dbconn))
	{
		//connection failed, exit with an error
		echo pg_errormessage($dbconn);
		exit;
	}
	return $dbconn;
}

function dbexecute($query,$connection=null)
{
	if (isset($connection))
	{
		$returnds = pg_query($connection, $query);
	}
	else
	{
		$connection = dbconnect();
		$returnds = pg_query($connection, $query);
		pg_close($connection);
	}
	
	if (!$returnds)
	{
		echo "Could not execute query!<br>";
	}
	return $returnds;
}
?>
