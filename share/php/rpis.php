<?php

$GLOBALS['LIKEMAP'] = array(
    '=' => 'LIKE',
    '!=' => 'NOT LIKE'
);

define('FILTER_REG','/^(.*?)\s*(>=|<=|>|<|!=|=)\s*(.*?)$/');

function getProjectDetails($dbh, $filters = array()) {
    $SELECT = 'SELECT DISTINCT
               pg.Program_ID as ID,
               pg.Program_Title as Title,
               pg.Program_Abstract as Abstract,
               pg.Program_StartDate as StartDate,
               pg.Program_EndDate as EndDate,
               pg.Program_Location as Location,
               pg.Program_FundSrc as Fund_Src,
               f.Fund_Source as Fund_Abbr,
               f.Fund_Name as Fund_Name';

    $FROM = 'FROM Programs pg
             LEFT OUTER JOIN ProjPeople ppg ON ppg.Program_ID = pg.Program_ID
             LEFT OUTER JOIN People p ON p.People_ID = ppg.People_ID
             LEFT OUTER JOIN Institutions inst ON inst.Institution_ID = p.People_Institution
             LEFT OUTER JOIN FundingSource f ON f.Fund_ID = pg.Program_FundSrc';

    $WHERE = 'WHERE pg.Program_Completed=1';

    foreach ($filters as $filter) {
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch(strtolower($matches[1])) {
                case 'projectid':
                    $WHERE .= " AND pg.Program_ID $matches[2] $matches[3]";
                    break;
                case 'fundsrc':
                    $WHERE .= " AND pg.Program_FundSrc $matches[2] $matches[3]";
                    break;
                case 'peopleid':
                    $WHERE .= " AND ppg.People_ID $matches[2] $matches[3]";
                    break;
                case 'peopleid_strict':
                    $WHERE .= " AND ppg.People_ID $matches[2] $matches[3] AND ppg.Project_ID = 0";
                    break;
                case 'institutionid':
                    $WHERE .= " AND inst.Institution_ID $matches[2] $matches[3]";
                    break;
            }
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
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch (strtolower($matches[1])) {
                case 'projectid':
                    $WHERE .= " AND pj.Program_ID $matches[2] $matches[3]";
                    break;
                case 'title':
                    $WHERE .= " AND pj.Project_Title " . $GLOBALS['LIKEMAP'][$matches[2]] . " \"$matches[3]\"";
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
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch (strtolower($matches[1])) {
                case 'peopleid':
                    $WHERE .= " AND p.People_ID $matches[2] $matches[3]";
                    break;
                case 'lastname':
                    $WHERE .= " AND p.People_LastName " . $GLOBALS['LIKEMAP'][$matches[2]] . " \"$matches[3]\"";
                    break;
                case 'firstname':
                    $WHERE .= " AND p.People_FirstName " . $GLOBALS['LIKEMAP'][$matches[2]] . " \"$matches[3]\"";
                    break;
                case 'projectid':
                    $WHERE .= " AND pp.Program_ID $matches[2] $matches[3]";
                    break;
                case 'taskid':
                    $WHERE .= " AND pp.Project_ID $matches[2] $matches[3]";
                    break;
                case 'roleid':
                    $WHERE .= " AND pp.Role_ID $matches[2] $matches[3]";
                    break;
            }
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
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch(strtolower($matches[1])) {
                case 'institutionid':
                    $WHERE .= " AND Institution_ID $matches[2] $matches[3]";
                    break;
                case 'name':
                    $WHERE .= " AND Institution_Name " . $GLOBALS['LIKEMAP'][$matches[2]] . " \"$matches[3]\"";
                    break;
                case 'projectid':
                    $FROM = " FROM (
                                        (
                                            SELECT People_ID
                                            FROM Projects
                                            LEFT JOIN ProjPeople ON Projects.Project_ID = ProjPeople.Project_ID
                                            WHERE Projects.Program_ID $matches[2] $matches[3]
                                        )
                                        UNION
                                        (
                                            SELECT People_ID
                                            FROM ProjPeople
                                        WHERE Program_ID $matches[2] $matches[3]
                                        )
                                    )
                                    AS T
                                    LEFT JOIN People ON T.People_ID = People.People_ID
                                    LEFT JOIN Institutions ON People.People_Institution = Institutions.Institution_ID";
                    break;
            }
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

    foreach ($filters as $filter) {
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch(strtolower($matches[1])) {
                case 'fundid':
                    $WHERE .= " AND Fund_ID $matches[2] $matches[3]";
                    break;
            }
        }
    }
    $stmt = $dbh->prepare("$SELECT $FROM $WHERE ORDER BY Fund_sort;");
    $stmt->execute();

    return $stmt->fetchAll();
}

?>
