<?php

function displayTaskStatus($tasks,$update=null,$personid=null)
{
    $projectID ="";
    $taskTitle="";
    $taskID ="";
    echo "d = new dTree('d');\n\n";

    $folderCount =1;
    $nodeCount = 0;

    $uid = getUID();

    $query = "

    SELECT registry_id, dataset_title FROM registry r

    INNER JOIN (
        SELECT SUBSTR(registry_id,0,17) AS rid, MAX(registry_id) AS maxreg
        FROM registry
        GROUP BY rid
        ORDER BY rid
        ) m

    ON r.registry_id = m.maxreg

    WHERE userid='$uid'
    ORDER BY registry_id
    ;";

    $results = dbexecute($query);

    echo "d.add($nodeCount,-1,'My Registries','');\n\n";
    $myNode = $nodeCount;
    $nodeCount++;

    echo "d.add($nodeCount,$myNode,'Others','javascript: d.o($nodeCount);','Other Registrations','','','',true);\n";
    $nodeCount++;

    while ($row = pg_fetch_row($results))
    {
        $status = 2;
        $title = $row[1];
        $registry_id = $row[0];

        $qs = "?regid=$registry_id";
        if (isset($personid)) $qs .= "&prsid=$personid";
        if (array_key_exists('as_user',$_GET)) $qs .= "&as_user=$_GET[as_user]";
        echo "d.add($nodeCount,$folderCount,'".addslashes("[$registry_id] $title")."','$qs','".addslashes("[$registry_id] $title")."','_self'";

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



    // Datasets Folder Sub
    echo "d.add($nodeCount,-1,'Datasets','');\n\n";
    $dsNode = $nodeCount;
    $nodeCount++;

    $folderCount=$nodeCount;

    foreach ($tasks as $task)
    {
        $taskID = $task['ID'];
        $taskTitle = $task->Title;
        $projectID = $task->Project['ID'];

        if ($taskID > 0)
        {
            $query = "select title,status,dataset_uid,dataset_udi from datasets where task_uid=$taskID and status=2";
        }
        else
        {
            $query = "select title,status,dataset_uid,dataset_udi from datasets where project_id=$projectID and status=2";

        }

        $results = dbexecute($query);

        $rnum = pg_num_rows($results);

        if ($rnum > 0)
        {

            echo "d.add($nodeCount,$dsNode,'".addslashes($taskTitle)."','javascript: d.o($nodeCount);','".addslashes($taskTitle)."','','','',true);\n";
            $nodeCount++;

            while ($row = pg_fetch_row($results))
            {
                $status = $row[1];
                $title = $row[0];
                $datasetid = $row[2];
                $dataset_udi = $row[3];

                $qs = "?uid=$datasetid";
                if (isset($personid)) $qs .= "&prsid=$personid";
                if (array_key_exists('as_user',$_GET)) $qs .= "&as_user=$_GET[as_user]";
                echo "d.add($nodeCount,$folderCount,'".addslashes("[$dataset_udi] $title")."','$qs','".addslashes("[$dataset_udi] $title")."','_self'";

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
