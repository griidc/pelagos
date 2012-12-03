<?php

$GLOBALS['LIKEMAP'] = array(
    '=' => 'LIKE',
    '!=' => 'NOT LIKE'
);

function getProjectDetails($dbh, $filters = array()) {
    $SELECT = 'SELECT DISTINCT
               pg.Program_ID as ID,
               pg.Program_Title as Title,
               pg.Program_Abstract as Abstract,
               pg.Program_StartDate as StartDate,
               pg.Program_EndDate as EndDate,
               pg.Program_Location as Location';

    $FROM = 'FROM Programs pg
             LEFT OUTER JOIN ProjPeople ppg ON ppg.Program_ID = pg.Program_ID
             LEFT OUTER JOIN People p ON p.People_ID = ppg.People_ID
             LEFT OUTER JOIN Institutions inst ON inst.Institution_ID = p.People_Institution';

    $WHERE = 'WHERE pg.Program_Completed=1';

    foreach ($filters as $filter) {
        $filt = preg_split('/=/',$filter);
        if ($filt[0] == 'projectId') {
            $WHERE .= " AND pg.Program_ID=$filt[1]";
        }
        if ($filt[0] == 'fundSrc') {
            $WHERE .= " AND pg.Program_FundSrc=$filt[1]";
        }
        if ($filt[0] == 'peopleId') {
            $WHERE .= " AND ppg.People_ID=$filt[1]";
        }
        if ($filt[0] == 'institutionId') {
            $WHERE .= " AND inst.Institution_ID=$filt[1]";
        }
    }

    $stmt = $dbh->prepare("$SELECT $FROM $WHERE ORDER BY Title;");
    $stmt->execute();
    $projects = $stmt->fetchAll();

    for ($i=0; $i<count($projects); $i++) {
        $stmt = $dbh->prepare("SELECT COUNT(DISTINCT Project_ID) FROM Projects WHERE Program_ID = ? AND Project_Completed = 1;");
        $stmt->execute(array($projects[$i]['ID']));
        $projects[$i]['SubTasks'] = $stmt->fetchColumn();
    }

    return $projects;
}

function getTaskDetails($dbh, $filters = array()) {
    $SELECT = 'SELECT DISTINCT
               pj.Project_ID as ID,
               pj.Project_Title as Title,
               pj.Project_Abstract as Abstract';

    $FROM = 'FROM Projects pj';

    $WHERE = 'WHERE 1';

    foreach ($filters as $filter) {
        if (preg_match('/^(.*?)\s*(!?=)\s*(.*?)$/',$filter,$matches)) {
            switch ($matches[1]) {
                case 'projectId':
                    $WHERE .= " AND pj.Program_ID $matches[2] $matches[3]";
                    break;
                case 'title':
                    $WHERE .= " AND pj.Project_Title " . $GLOBALS['LIKEMAP'][$matches[2]] . " '$matches[3]'";
                    break;
            }
        }
    }

    $stmt = $dbh->prepare("$SELECT $FROM $WHERE ORDER BY Title;");
    $stmt->execute();
    $tasks = $stmt->fetchAll();

    return $tasks;
}

function getPeopleDetails($dbh, $filters = array()) {
    $SELECT = 'SELECT DISTINCT
               p.People_ID AS ID,
               p.People_Title AS Title,
               p.People_LastName AS LastName,
               p.People_FirstName AS FirstName,
               inst.Institution_ID AS Institution_ID,
               inst.Institution_Name AS Institution_Name';

    $FROM = 'FROM People p
             LEFT OUTER JOIN ProjPeople pp ON pp.People_ID = p.People_ID
             LEFT OUTER JOIN Institutions inst ON inst.Institution_ID = p.People_Institution';

    $WHERE = 'WHERE 1';

    foreach ($filters as $filter) {
        $filt = preg_split('/=/',$filter);
        switch ($filt[0]) {
            case 'peopleId':
                $WHERE .= " AND p.People_ID=$filt[1]";
                break;
            case 'lastName':
                $WHERE .= " AND p.People_LastName LIKE \"$filt[1]\"";
                break;
            case 'firstName':
                $WHERE .= " AND p.People_FirstName LIKE \"$filt[1]\"";
                break;
            case 'projectId':
                $WHERE .= " AND pp.Program_ID=$filt[1]";
                break;
            case 'taskId':
                $WHERE .= " AND pp.Project_ID=$filt[1]";
                break;
            case 'roleId':
                $WHERE .= " AND pp.Role_ID=$filt[1]";
                break;
        }
    }

    $stmt = $dbh->prepare("$SELECT $FROM $WHERE ORDER BY LastName, FirstName;");
    $stmt->execute();

    return $stmt->fetchAll();
}

function getInstitutionDetails($dbh, $filters = array()) {
    $SELECT = 'SELECT DISTINCT
               Institution_ID as ID,
               Institution_Name as Name';

    $FROM = 'FROM Institutions';

    $WHERE = 'WHERE 1';

    foreach ($filters as $filter) {
        $filt = preg_split('/=/',$filter);
        if ($filt[0] == 'institutionId') {
            $WHERE .= " AND Institution_ID=$filt[1]";
        }
        if ($filt[0] == 'name') {
            $WHERE .= " AND Institution_Name LIKE '$filt[1]'";
        }
        if ($filt[0] == 'projectId') {
            $FROM = " FROM (
                                (
                                    SELECT People_ID
                                    FROM Projects
                                    LEFT JOIN ProjPeople ON Projects.Project_ID = ProjPeople.Project_ID
                                    WHERE Projects.Program_ID = $filt[1]
                                ) 
                                UNION
                                (
                                    SELECT People_ID
                                    FROM ProjPeople
                                WHERE Program_ID = $filt[1]
                                ) 
                            )
                            AS T
                            LEFT JOIN People ON T.People_ID = People.People_ID
                            LEFT JOIN Institutions ON People.People_Institution = Institutions.Institution_ID";
        }
    }

    $stmt = $dbh->prepare("$SELECT $FROM $WHERE ORDER BY Name;");
    $stmt->execute();

    return $stmt->fetchAll();
}

function getFundingSources($dbh, $filters = array()) {
    $SELECT = 'SELECT DISTINCT
               Fund_ID AS ID,
               Fund_Source as Abbr,
               Fund_Name as Name';

    $FROM = 'FROM FundingSource';

    $WHERE = 'WHERE 1';

    if (is_array($filters)) {
        foreach ($filters as $filter) {
            if (preg_match('/(>=|<=|>|<|=)/',$filter,$matches)) {
                $filt = preg_split('/>=|<=|>|<|=/',$filter);
                if ($filt[0] == 'fundId') {
                    $WHERE .= " AND Fund_ID$matches[1]$filt[1]";
                }
            }
        }
    }
    $stmt = $dbh->prepare("$SELECT $FROM $WHERE ORDER BY Fund_sort;");
    $stmt->execute();

    return $stmt->fetchAll();
}

?>
