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
    $SELECT = "SELECT DISTINCT title, status, dataset_uid, d.dataset_udi AS udi,
               CASE WHEN status = 2 THEN 1 WHEN status = 1 THEN 2 ELSE 0 END AS identified,
               CASE WHEN registry_id IS NULL THEN 0 ELSE 1 END AS registered,
               CASE WHEN metadata_dl_status IS NULL OR metadata_dl_status != 'Completed' THEN 0 WHEN metadata_status = 'Completed' THEN 1 ELSE 2 END AS metadata,
               CASE WHEN dataset_download_status = 'done' THEN 1 ELSE 0 END AS availability,
               CASE WHEN access_status = 'None' THEN 1 WHEN access_status = 'Requires Author''s Approval' THEN 2 ELSE 0 END AS accessibility";
    $FROM = 'FROM datasets d
             LEFT JOIN (
                 registry r2
                 INNER JOIN (
                     SELECT MAX(registry_id) AS MaxID
                     FROM registry
                     GROUP BY dataset_udi
                 ) m
             ON r2.registry_id = m.MaxID
             ) r
             ON r.dataset_udi = d.dataset_udi';
    $dbh = getDBH('GOMRI');
    for ($i=0;$i<count($projects);$i++) {
        $pi = getPeopleDetails(getDBH('RPIS'),array('projectId='.$projects[$i]['ID'],'roleId=1'));
        $projects[$i]['PI'] = $pi[0];
        $projects[$i]['Institutions'] = getInstitutionDetails(getDBH('RPIS'),array('projectId='.$projects[$i]['ID']));
        $taskFilter = array('projectId='.$projects[$i]['ID']);
        if (isset($GLOBALS['config']['exclude']['tasks'])) {
            foreach ($GLOBALS['config']['exclude']['tasks'] as $exclude) {
                $taskFilter[] = "title!=$exclude";
            }
        }
        $tasks = getTaskDetails(getDBH('RPIS'),$taskFilter);
        if (count($tasks) > 0) {
            for ($j=0;$j<count($tasks);$j++) {
                $stmt = $dbh->prepare("$SELECT $FROM WHERE task_uid=".$tasks[$j]['ID'].' AND status>0 ORDER BY udi;');
                $stmt->execute();
                $datasets = $stmt->fetchAll();
                if (is_array($datasets)) {
                    $tasks[$j]['datasets'] = $datasets;
                }
            }
            $projects[$i]['tasks'] = $tasks;
        }
        else {
            $stmt = $dbh->prepare("$SELECT $FROM WHERE project_id=".$projects[$i]['ID'].' AND status>0 ORDER BY udi;');
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
