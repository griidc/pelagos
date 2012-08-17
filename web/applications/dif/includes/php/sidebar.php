<?PHP error_reporting(0);?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en-GB">
<head>
<link rel="stylesheet" type="text/css" href="https://proteus.tamucc.edu/~mvandeneijnden/dif/_styles.css" media="screen"> 
</head>
<body bgcolor="#cdcdcd">






<?php



echo "<table class=cleair>
    <tbody class=tbody>

<tr><td>";


echo " <h2 class=\"title\" align=center>Showing All Tasks for ".$firstName." ".$lastName."<hr /></FONT>";
echo "</h2></td></tr><tr><td><div style=width:100%;height:800px;overflow:auto; BGCOLOR=#efefef>";

	displayTaskStatusByName($lastName,$firstName);



echo "</div></td></tr> </tbody> </table>";
?>



</body>
</html>

<?php
//showTable('select * from datasets');

function displayTaskStatusByName($lastName, $firstName)
{
	$baseurl = 'https://proteus.tamucc.edu/~mvandeneijnden/ProjectDB/getTaskDetails.php?maxResults=-1';
	$url = $baseurl . "&lastName=$lastName&firstName=$firstName";
	$doc = simplexml_load_file($url);
	$tasks = $doc->xpath('Task');
        echo '<ol class=" tree">';
//	echo '<ol class="tree">';
	foreach ($tasks as $task)
	{
		$taskID = $task['ID'];
		
		$taskTitle = $task->Title;
		echo '<li class="folder"><label for="'.$taskID.'">'.$taskTitle.'</label> <input type="checkbox" checked id="'.$taskID.'"/>';
		//echo "<tr><td  colspan=2><li class=\"redfile\">$taskTitle ($taskID)</td></tr><tr>";
		$query = "select title,status,dataset_uid from datasets where task_uid=$taskID";
		$results = dbexecute($query);
		echo '<ol>';
		while ($row = pg_fetch_row($results)) 
		{
			$status = $row[1];
			$title = $row[0];
			$datasetid = $row[2];
			//echo "<td>$title</td>";
						
			switch ($status)
			{
				case null:
				echo '<li class="redfile">';
				//echo '<td align="center" valign="middle"><img src="white_bobble.png" width="10"></td>';
				break;
				case 0:
				echo '<li class="redfile">';
				//echo '<td align="center" valign="middle"><img src="red_bobble.png" width="10"></td>';
				break;
				case 1:
				echo '<li class="yellowfile">';
				//echo '<td align="center" valign="middle"><img src="yellow_bobble.png" width="10"></td>';
				break;
				case 2:
				echo '<li class="greenfile">';
				//echo '<td align="center" valign="middle"><img src="green_bobble.png" width="10"></td>';
				break;
			}
                        echo '<a href="https://proteus.tamucc.edu/Form2?uid='.$datasetid.'">'.$title.'</a></li>';

		}
		echo '</ol></li>';
	}
	echo '</ol>';
	
}

#region PostGreSQL stuff
function dbconnect()
{
	$username='admin';
	$password='password';
	$database='gomri';
	$dbserver='proteus.tamucc.edu';
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

function showTable($query)
{
	$results = dbexecute($query);
	echo "<p><h2>Table:</h2></p><table border=1><tr>";

	for ($i = 0; $i < pg_numfields($results);$i++)
	{
		echo '<th>' . pg_field_name($results,$i) . '</th>';
	}
	echo '</tr>';

	while ($row = pg_fetch_row($results)) 
	{
		echo "<tr>";
		foreach ($row as $field)
		{
			echo "<td>" . $field . "</td>";
		}
		echo "</tr>";
	}	
	echo '</table>'; 
}
#end region

?>
