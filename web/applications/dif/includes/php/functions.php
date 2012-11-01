<?PHP
// Module: functions.php
// Author(s): Jew-Lee I. Lann, Michael van den Eijnden
// Last Updated: 21 August 2012
// Parameters: None
// Returns: functions
// Purpose: Has several functions for DIF.

$change=array("01"=>"Jan.","02"=>"Feb.","03"=>"Mar.","04"=>"Apr.","05"=>"May ","06"=>"Jun.","07"=>"Jul.","08"=>"Aug.","09"=>"Sep.","10"=>"Oct.","11"=>"Nov.","12"=>"Dec.");

$isGroupAdmin = false;

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

function makeTaskGrouping($tasks, $which) {
	foreach ($tasks as $task){
                $dbOptionValue = $task['ID']. '|' . $task->Project['ID'];
		$dbOption = $taskTitle;
		 echo "if (chosen == \"$dbOptionValue\") { ";
		 callPeople($which, $task);
		 echo" }\n\n";
	}
}
		






function callPeople($w, $task) {
    $bb=array();
    $he = "\nselboxs.options[selboxs.options.length] = new \nOption('[SELECT]', '999');";
    if ($w=="s"){$b=array($he);}else{$b =array();}
    $peops = $task->xpath('Researchers/Person');
    foreach ($peops as $peoples) {
         $personID = $peoples['ID'];
         $bool = 0;
         $LastName = preg_replace('/\'/','\\\'',$peoples->LastName);
         $FirstName = preg_replace('/\'/','\\\'',$peoples->FirstName);
         $Email = preg_replace('/\'/','\\\'',$peoples->Email);
         if (!$Email){}else{$Email = " <$Email>";}
         $line = "\nselbox$w.options[selbox$w.options.length] = new \nOption('$LastName, $FirstName $Email', $personID, '', $bool);";
         array_push($bb, $line );
    }
    array_unique($bb);
    sort($bb);
    $result = array_merge($b, $bb);
    foreach($result as $ribbit){ echo $ribbit; }
}








   	
function getPersonOptionList($whom, $ti) {
    $filters = '';
    if ($ti > 0)
    {
        $filters .= "?taskID=$ti";
    }
    $url = $GLOBALS['RPIS_people_baseurl'].$filters;
    $doc = simplexml_load_file($url);
    $buildarray=array('<option value="">[SELECT]</option>');
    $peops = $doc->xpath('Person');
    foreach ($peops as $peoples) {
        $personID = $peoples['ID'];
        $line= "<option value=\"$personID\"";
        if ($whom==$personID){$line .= " SELECTED";}
        $line.= ">$peoples->LastName, $peoples->FirstName ($peoples->Email)</option>";
        array_push($buildarray, $line );
    }
    $result = array_unique($buildarray);
    foreach($result as $ribbit){ echo $ribbit; }
    unset($doc);
}

function getTaskOptionList($tasks, $what) {
    $maxLength = 200;
	foreach ($tasks as $task){
		if (strlen($task->Title) > $maxLength){
			$taskTitle=substr($task->Title,0,$maxLength).'...';
		} else {
			$taskTitle=$task->Title;
		}
		//$dbOptionValue = $task['ID'];
                $dbOptionValue = $task['ID']. '|' . $task->Project['ID'];
		$dbOption = $taskTitle;

		echo "<option value=\"$dbOptionValue\"";
		if ($what==$dbOptionValue){echo " SELECTED";}
		echo ">$dbOption</option>";
	}
	unset($doc);
}

function getTasks($ldap,$baseDN,$userDN,$firstName,$lastName) {
    global $isGroupAdmin;
    $switch = '?'.'maxResults=-1';
    $tasks = array();
    if (isAdmin())
    {
        $doc = simplexml_load_file($GLOBALS['RPIS_task_baseurl'].$switch.'&cached=true');
        $tasks = array_merge($tasks,$doc->xpath('Task'));
    }
    else
    {
        $groupDNs = getDNs($ldap,'ou=groups,'.$baseDN,"(&(member=$userDN)(cn=administrators))");

        foreach ($groupDNs as $group)
        {
            if (!is_array($group)) continue;
            preg_match('/ou=([^,]+)/',$group['dn'],$matches);
            $filters = "&projectTitle=$matches[1]";
            $doc = simplexml_load_file($GLOBALS['RPIS_task_baseurl'].$switch.$filters);
            $tasks = array_merge($tasks,$doc->xpath('Task'));
            $GLOBALS['isGroupAdmin'] = true;
        }

        if (count($groupDNs) == 0)
        {
            $filters = "&lastName=$lastName&firstName=$firstName";
            $doc = simplexml_load_file($GLOBALS['RPIS_task_baseurl'].$switch.$filters);
            $tasks = array_merge($tasks,$doc->xpath('Task'));
        }
    }

    return $tasks;
}

function displayTaskStatus($tasks,$update=null,$personid=null)
{
    echo "d = new dTree('d');\n\n";
    echo "d.add(0,-1,'Datasets','');\n\n";
    $nodeCount = 1;
    $folderCount =1;
    foreach ($tasks as $task)
    {
        $taskID = $task['ID'];
        
        $taskTitle = $task->Title;
        $projectID = $task->Project[ID];

        echo "d.add($nodeCount,0,'".addslashes($taskTitle)."','javascript: d.o($nodeCount);','".addslashes($taskTitle)."','','','',true);\n";
        $nodeCount++;
        
        if ($taskID > 0)
        {
            $query = "select title,status,dataset_uid from datasets where task_uid=$taskID";
        }
        else
        {
            $query = "select title,status,dataset_uid from datasets where project_id=$projectID";

        }   	
        
        $results = dbexecute($query);
        
        while ($row = pg_fetch_row($results)) 
        {
            $status = $row[1];
            $title = $row[0];
            $datasetid = $row[2];
            
            if (isset($personid))
            {
                echo "d.add($nodeCount,$folderCount,'".addslashes($title)."','?uid=$datasetid&prsid=$personid','".addslashes($title)."','_self'";
            }
            else
            {
                echo "d.add($nodeCount,$folderCount,'".addslashes($title)."','?uid=$datasetid','".addslashes($title)."','_self'";
            }
            
            
            switch ($status)
            {
                case null:
                echo ",'/dif/images/red_bobble.png');\n";
                break;
                case 0:
                echo ",'/dif/images/red_bobble.png');\n";
                break;
                case 1:
                echo ",'/dif/images/yellow_bobble.png');\n";
                break;
                case 2:
                echo ",'/dif/images/green_bobble.png');\n";
                break;
                default:
                echo ");\n";
                break;
            }
            $nodeCount++;
        }
        $folderCount=$nodeCount;
    }
    if ($update)
    {
        echo 'document.getElementById("dstree").innerHTML=d;';
    }
        else
    {
        echo "\ndocument.write(d);\n";
    }
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

function filterTasks($tasks, $person)
{
    $filteredTasks = array();
    foreach ($tasks as $task)
    {
        if (isset($person) and $person>0)
        {
            $peoples = $task->xpath('Researchers/Person');
            foreach ($peoples as $people) 
            {
                $personid = $people['ID'];
                if ($personid == $person) 
                {
                    array_push($filteredTasks, $task);
                }
            }
        }
        else
        {
            array_push($filteredTasks, $task);
        }
    }
    
    return $filteredTasks;
}

function helps($for, $ht, $tip){ echo "\n<label for=\"$for\"><b>$ht: </b><span id=\"$tip\" style=\"float:right;\"> <IMG SRC=\"/dif/images/info.png\"></span></label>\n"; }
?>

