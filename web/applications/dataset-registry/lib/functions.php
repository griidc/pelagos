<?php
// @codingStandardsIgnoreFile

function displayTaskStatus($tasks,$update=null,$personid=null)
{
    $projects = array();
    $projectIDs = array();

    foreach ($tasks as $task)
    {
        $projectID = (integer) $task->Project['ID'];
        $projectTitle = (string) $task->Project->Title;
        
        if (!in_array($projectID, $projectIDs)) {
            $projects[] = array('title'=>$projectTitle, 'projectID'=>$projectID);
            $projectIDs[] = $projectID;
        }
    }
    
    sort($projects);
    
    $projectID ="";
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
        $title_html = preg_replace('/[\r\n]/','<br>',htmlspecialchars($row[1],ENT_QUOTES));
        $title_plain = preg_replace('/[\r\n]/','\n',htmlspecialchars($row[1],ENT_QUOTES));
        $registry_id = $row[0];

        $qs = "?regid=$registry_id";
        if (isset($personid)) $qs .= "&prsid=$personid";
        if (array_key_exists('as_user',$_GET)) $qs .= "&as_user=$_GET[as_user]";
        echo "d.add($nodeCount,$folderCount,'[$registry_id] $title_html','$qs','[$registry_id] $title_plain','_self'";

        switch ($status)
        {
            case null:
            echo ",'includes/images/red_bobble.png');\n";
            break;
            case 0:
            echo ",'includes/images/red_bobble.png');\n";
            break;
            case 1:
            echo ",'includes/images/yellow_bobble.png');\n";
            break;
            case 2:
            echo ",'includes/images/green_bobble.png');\n";
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

    foreach ($projects as $project)
    {
        $projectID = $project['projectID'];
        $projectTitle = $project['title'];

        $query = "select title,status,dataset_uid,dataset_udi from datasets where project_id=$projectID and status=2 order by dataset_udi;";

        $results = dbexecute($query);

        $rnum = pg_num_rows($results);

        if ($rnum > 0)
        {
            echo "d.add($nodeCount,$dsNode,'".addslashes($projectTitle)."','javascript: d.o($nodeCount);','".addslashes($projectTitle)."','','','',true);\n";
            $nodeCount++;

            while ($row = pg_fetch_row($results))
            {
                //var_dump($row[0]);
                
                $status = $row[1];
                $title_html = preg_replace('/[\r\n]/','<br>',htmlspecialchars($row[0],ENT_QUOTES));
                $title_plain = preg_replace('/[\r\n]/','\n',htmlspecialchars($row[0],ENT_QUOTES));
                $datasetid = $row[2];
                $dataset_udi = $row[3];

                $qs = "?uid=$datasetid";
                if (isset($personid)) $qs .= "&prsid=$personid";
                if (array_key_exists('as_user',$_GET)) $qs .= "&as_user=$_GET[as_user]";
                echo "d.add($nodeCount,$folderCount,'[$dataset_udi] $title_html','$qs','[$dataset_udi] $title_plain','_self'";

                switch ($status)
                {
                    case null:
                    echo ",'includes/images/red_bobble.png');\n";
                    break;
                    case 0:
                    echo ",'includes/images/red_bobble.png');\n";
                    break;
                    case 1:
                    echo ",'includes/images/yellow_bobble.png');\n";
                    break;
                    case 2:
                    echo ",'includes/images/green_bobble.png');\n";
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
