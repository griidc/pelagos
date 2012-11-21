<?php

function getDBH($db) {
    $dbh = new PDO($GLOBALS['config'][$db.'_DB']['connstr'],
                   $GLOBALS['config'][$db.'_DB']['username'],
                   $GLOBALS['config'][$db.'_DB']['password'],
                   array(PDO::ATTR_PERSISTENT => true));

    if ($db == 'RPIS') {
        $stmt = $dbh->prepare('SET character_set_client = utf8;');
        $stmt->execute();
        $stmt = $dbh->prepare('SET character_set_results = utf8;');
        $stmt->execute();
    }

    return $dbh;
}

function getTasksAndDatasets($projects) {
    $SELECT = 'SELECT title, status, dataset_uid, d.dataset_udi AS udi, CASE WHEN registry_id IS NULL THEN 0 ELSE 1 END AS registered';
    $FROM = 'FROM datasets d LEFT OUTER JOIN registry r ON r.dataset_udi = d.dataset_udi';
    $dbh = getDBH('GOMRI');
    for ($i=0;$i<count($projects);$i++) {
        $pi = getPeopleDetails(getDBH('RPIS'),array('projectId='.$projects[$i]['ID'],'roleId=1'));
        $projects[$i]['PI'] = $pi[0];
        $projects[$i]['Institutions'] = getInstitutionDetails(getDBH('RPIS'),array('projectId='.$projects[$i]['ID']));
        $tasks = getTaskDetails(getDBH('RPIS'),array('projectId='.$projects[$i]['ID']));
        if (count($tasks) > 0) {
            for ($j=0;$j<count($tasks);$j++) {
                $stmt = $dbh->prepare("$SELECT $FROM WHERE task_uid=".$tasks[$j]['ID'].' ORDER BY udi;');
                $stmt->execute();
                $datasets = $stmt->fetchAll();
                if (is_array($datasets)) {
                    $tasks[$j]['datasets'] = $datasets;
                }
            }
            $projects[$i]['tasks'] = $tasks;
        }
        else {
            $stmt = $dbh->prepare("$SELECT $FROM WHERE project_id=".$projects[$i]['ID'].' ORDER BY udi;');
            $stmt->execute();
            $datasets = $stmt->fetchAll();
            if (is_array($datasets)) {
                $projects[$i]['datasets'] = $datasets;
            }
        }
    }
    return $projects;
}

?>
