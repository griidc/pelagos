<?php

$change = array("01"=>"Jan.","02"=>"Feb.","03"=>"Mar.","04"=>"Apr.","05"=>"May ","06"=>"Jun.","07"=>"Jul.","08"=>"Aug.","09"=>"Sep.","10"=>"Oct.","11"=>"Nov.","12"=>"Dec.");

function test_print($item2, $key, $prefix) {
    echo "<option value=".$key;
    if ($prefix==$key){echo " SELECTED";} echo ">".$item2."</option>\n";
}

function displayTaskStatus($tasks,$update=null,$personid=null,$filterstatus=null)
{
    $projectID ="";
    $taskTitle="";
    $taskID ="";
    echo "d = new dTree('d');\n\n";
    echo "d.add(0,-1,'Datasets','');\n\n";
    $nodeCount = 1;
    $folderCount =1;
    foreach ($tasks as $task)
    {
        $taskID = $task['ID'];
        $taskTitle = $task->Title;
        $projectID = $task->Project['ID'];

        echo "d.add($nodeCount,0,'".addslashes($taskTitle)."','javascript: d.o($nodeCount);','".addslashes($taskTitle)."','','','',true);\n";
        $nodeCount++;
        
        if ($taskID > 0)
        {
            $query = "select title,status,dataset_uid,dataset_udi from datasets where task_uid=$taskID order by dataset_udi";
        }
        else
        {
            $query = "select title,status,dataset_uid,dataset_udi from datasets where project_id=$projectID order by dataset_udi";

        }   	
        
        $results = dbexecute($query);
        
        while ($row = pg_fetch_row($results)) 
        {
            $status = $row[1];
            $title = $row[0];
            $datasetid = $row[2];
            $dataset_udi = $row[3];

            $qs = "uid=$datasetid";
            if (isset($personid)) { $qs .= "&prsid=$personid"; }
			if (array_key_exists('as_user',$_GET)) { $qs .= "&as_user=$_GET[as_user]"; }
            
			if ((isset($filterstatus) AND $filterstatus==$status) OR (!isset($filterstatus)))
			{
			
				echo "d.add($nodeCount,$folderCount,'".addslashes("[$dataset_udi] $title")."','?$qs','".addslashes("[$dataset_udi] $title")."','_self'";
				
				switch ($status)
				{
					case null:
					echo ",'/dataset-monitoring/includes/images/x.png');\n";
					break;
					case 0:
					echo ",'/dataset-monitoring/includes/images/x.png');\n";
					break;
					case 1:
					echo ",'/dataset-monitoring/includes/images/triangle_yellow.png');\n";
					break;
					case 2:
					echo ",'/dataset-monitoring/includes/images/check.png');\n";
					break;
					default:
					echo ");\n";
					break;
				}
				$nodeCount++;
			
			}
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

?>
