<?php

// Module: sidebar.php
// Author(s): Michael van den Eijnden
// Last Updated: 21 August 2012
// Parameters: None
// Returns: Folder list op datasets by task.
// Purpose: To return data gather from the RPIS service and database to show a list of datasets by task.

include_once '/usr/local/share/GRIIDC/php/ldap.php';
include_once '/usr/local/share/GRIIDC/php/drupal.php';

?>
<link rel="StyleSheet" href="/dif/includes/css/dtree.css" type="text/css" />
<script type="text/javascript" src="/dif/includes/js/dtree.js"></script>
<?php
echo "<table class=cleair><tbody class=tbody><tr><td>";
echo "<h2 class=\"title\" align=center>Tasks and datasets for ".$firstName." ".$lastName."<hr /></FONT>";
echo "</h2></td></tr><tr><td><div style=width:100%;height:800px;overflow:auto; BGCOLOR=#efefef>";

displayTaskStatusByName($lastName,$firstName);

echo "</div></td></tr> </tbody> </table>";

function displayTaskStatusByName($lastName, $firstName)
{
    $baseurl = 'http://griidc.tamucc.edu/services/RPIS/getTaskDetails.php';
    $switch = '?'.'maxResults=-1';
    
    $tasks = array();
    
    if (isAdmin())
    {
        $doc = simplexml_load_file($baseurl.$switch);
        $tasks = array_merge($tasks,$doc->xpath('Task'));
    }
    else
    {
        $uid = getDrupalUserName();
        $basedn = 'dc=griidc,dc=org';
        $ldap = connectLDAP('triton.tamucc.edu');
        $userdns = getDNs($ldap,$basedn,"uid=$uid");
        $userdn = $userdns[0]['dn'];
        $groupdns = getDNs($ldap,'ou=groups,'.$basedn,"(&(member=$userdn)(cn=administrators))");

        foreach ($groupdns as $group)
        {
            if (!is_array($group)) continue;
            preg_match('/ou=([^,]+)/',$group['dn'],$matches);
            $filters = "&projectTitle=$matches[1]";
            $doc = simplexml_load_file($baseurl.$switch.$filters);
            $tasks = array_merge($tasks,$doc->xpath('Task'));
        }
    
        if (count($groupdns) == 0)
        {
            $filters = "&lastName=$lastName&firstName=$firstName";
            $doc = simplexml_load_file($baseurl.$switch.$filters);
            $tasks = array_merge($tasks,$doc->xpath('Task'));
        }
    }
    
    echo "<div class=\"dtree\">\n";
	echo "<script type=\"text/javascript\">\n\n";
	echo "d = new dTree('d');\n\n";
	echo "d.add(0,-1,'Datasets','');\n\n";
	$nodeCount = 1;
	$folderCount =1;
	foreach ($tasks as $task)
    {
        $taskID = $task['ID'];
		
		$taskTitle = $task->Title;
			
		echo "d.add($nodeCount,0,'".addslashes($taskTitle)."','javascript: d.o($nodeCount);','".addslashes($taskTitle)."','','','',true);\n";
		$nodeCount++;
		
		$query = "select title,status,dataset_uid from datasets where task_uid=$taskID";
		$results = dbexecute($query);
		
		while ($row = pg_fetch_row($results)) 
		{
			$status = $row[1];
			$title = $row[0];
			$datasetid = $row[2];

			switch ($status)
			{
				case null:
				echo "d.add($nodeCount,$folderCount,'".addslashes($title)."','/dif?uid=$datasetid','".addslashes($title)."','_self','/dif/images/red_bobble.png');\n";
				break;
				case 0:
				echo "d.add($nodeCount,$folderCount,'".addslashes($title)."','/dif?uid=$datasetid','".addslashes($title)."','_self','/dif/images/red_bobble.png');\n";
				break;
				case 1:
				echo "d.add($nodeCount,$folderCount,'".addslashes($title)."','/dif?uid=$datasetid','".addslashes($title)."','_self','/dif/images/yellow_bobble.png');\n";
				break;
				case 2:
				echo "d.add($nodeCount,$folderCount,'".addslashes($title)."','/dif?uid=$datasetid','".addslashes($title)."','_self','/dif/images/green_bobble.png');\n";
				break;
			}
		$nodeCount++;
		}
		$folderCount=$nodeCount;
	}
	echo "\ndocument.write(d);\n";
	echo "</script>\n</div>\n";
	
}

function dbconnect()
{
    include 'dbGomri.php';
	//Connect to database
	$connString = "host=$dbserver port=$port dbname=$database user=$username password=$password";
 	$dbconn = pg_connect($connString)or die("Couldn't Connect : " . pg_last_error());
	//Check it
	if(!($dbconn))
	{
		//connection failed, exit with an error
		echo 'Database Connection Failed: ' . pg_errormessage($dbconn);#
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
