<?php

$GLOBALS['PGSQL_LIKE_MAP'] = array(
    '=' => 'ILIKE',
    '!=' => 'NOT ILIKE'
);

if (!defined('FILTER_REG')) define('FILTER_REG','/^(.*?)\s*(>=|<=|>|<|!=|=)\s*(.*?)$/');

function countDatasets($dbh, $filters = array()) {
    $SELECT = 'SELECT COUNT(d.dataset_udi)';

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

    $WHERE = 'WHERE TRUE';

    foreach ($filters as $filter) {
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch (strtolower($matches[1])) {
                case 'taskid':
                    $WHERE .= " AND task_uid $matches[2] $matches[3]";
                    break;
                case 'projectid':
                    $WHERE .= " AND project_id $matches[2] $matches[3]";
                    break;
                case 'title':
                    $WHERE .= " AND title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'abstract':
                    $WHERE .= " AND abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'status':
                    $WHERE .= " AND status $matches[2] $matches[3]";
                    break;
                case 'filter':
                    $WHERE .= " AND (title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    $WHERE .= " OR abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]')";
                    break;
            }
        }
    }

    $stmt = $dbh->prepare("$SELECT $FROM $WHERE;");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getDatasets($dbh, $filters = array()) {
    $SELECT = 'SELECT
               d.dataset_udi udi,
               CASE WHEN dataset_title IS NULL THEN title ELSE dataset_title END AS title,
               CASE WHEN dataset_abstract IS NULL THEN abstract ELSE dataset_abstract END AS abstract,

               dataset_uid,
               task_uid,
               project_id,
               dataset_type,
               dataset_for,
               size,
               observation,
               approach,
               start_date,
               end_date,
               geo_location,
               historic_links,
               meta_editor,
               meta_standards,
               point,
               national,
               ethical,
               remarks,
               primary_poc,
               secondary_poc,
               logname,
               status,
               datafor,

               registry_id,
               data_server_type,
               url_data,
               url_metadata,
               data_source_pull,
               doi,
               username,
               password,
               availability_date,
               access_status,
               access_period,
               access_period_start,
               access_period_weekdays,
               dataset_poc_name,
               dataset_poc_email,
               submittimestamp,
               userid,
               authentication,
               generatedoi,
               dataset_download_start_datetime,
               dataset_download_size,
               dataset_download_end_datetime,
               dataset_filename,
               dataset_uuid,
               dataset_metadata,
               dataset_download_error_log,
               dataset_download_status
               ';

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

    $WHERE = 'WHERE TRUE';

    foreach ($filters as $filter) {
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch (strtolower($matches[1])) {
                case 'udi':
                    $WHERE .= " AND d.dataset_udi " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'taskid':
                    $WHERE .= " AND task_uid $matches[2] $matches[3]";
                    break;
                case 'projectid':
                    $WHERE .= " AND project_id $matches[2] $matches[3]";
                    break;
                case 'title':
                    $WHERE .= " AND title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'abstract':
                    $WHERE .= " AND abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'status':
                    $WHERE .= " AND status $matches[2] $matches[3]";
                    break;
                case 'filter':
                    $WHERE .= " AND (title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    $WHERE .= " OR abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]')";
                    break;
            }
        }
    }

    $stmt = $dbh->prepare("$SELECT $FROM $WHERE ORDER BY udi;");
    $stmt->execute();
    return $stmt->fetchAll();
}

?>
