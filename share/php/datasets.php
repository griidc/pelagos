<?php

$GLOBALS['PGSQL_LIKE_MAP'] = array(
    '=' => 'ILIKE',
    '!=' => 'NOT ILIKE'
);

$GLOBALS['IS_MAP'] = array(
    '=' => 'IS',
    '!=' => 'IS NOT'
);

$GLOBALS['NULL_MAP'] = array(
    'null' => 'NULL',
    'NULL' => 'NULL',
    '0' => 'NULL',
    'false' => 'NULL'
);

if (!defined('FILTER_REG')) define('FILTER_REG','/^(.*?)\s*(>=|<=|>|<|!=|=)\s*(.*?)$/');

$GLOBALS['REGISTRY_FIELDS'] = array(
    "registry_id",
    "data_server_type",
    "url_data",
    "url_metadata",
    "data_source_pull",
    "doi",
    "username",
    "password",
    "availability_date",
    "access_status",
    "access_period",
    "access_period_start",
    "access_period_weekdays",
    "dataset_poc_name",
    "dataset_poc_email",
    "submittimestamp",
    "userid",
    "authentication",
    "generatedoi",
    "dataset_download_start_datetime",
    "dataset_download_size",
    "dataset_download_end_datetime",
    "dataset_filename",
    "dataset_uuid",
    "dataset_metadata metadata_filename",
    "dataset_download_error_log",
    "dataset_download_status",
    "to_char(submittimestamp,'YYYY') as year"
);

$GLOBALS['DIF_FIELDS'] = array(
    "dataset_uid",
    "task_uid",
    "project_id",
    "dataset_type",
    "dataset_for",
    "size",
    "observation",
    "approach",
    "start_date",
    "end_date",
    "geo_location",
    "historic_links",
    "meta_editor",
    "meta_standards",
    "point",
    "national",
    "ethical",
    "remarks",
    "primary_poc",
    "secondary_poc",
    "logname",
    "status",
    "datafor"
);

$GLOBALS['REGISTRY_OVERRIDE_FIELDS'] = array(
    "CASE WHEN dataset_title IS NULL THEN title ELSE dataset_title END AS title",
    "CASE WHEN dataset_abstract IS NULL THEN abstract ELSE dataset_abstract END AS abstract"
);

$GLOBALS['IDENTIFIED_FROM'] = 'FROM datasets d
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

$GLOBALS['REGISTERED_FROM'] = 'FROM registry r
                               INNER JOIN (
                                   SELECT MAX(registry_id) AS MaxID
                                   FROM registry
                                   GROUP BY substr(registry_id,1,16)
                               ) m
                               ON r.registry_id = m.MaxID
                               LEFT JOIN datasets d
                               ON d.dataset_udi = r.dataset_udi';

function count_identified_datasets($dbh, $filters = array()) {
    $SELECT = 'SELECT COUNT(d.dataset_udi)';

    $WHERE = build_where($filters);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[IDENTIFIED_FROM] $WHERE;");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function count_registered_datasets($dbh, $filters = array()) {
    $SELECT = 'SELECT COUNT(registry_id)';

    $WHERE = build_where($filters,true);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[REGISTERED_FROM] $WHERE;");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function get_identified_datasets($dbh, $filters = array(), $order_by = 'udi') {
    $SELECT = 'SELECT ' . 
              implode(',',$GLOBALS['DIF_FIELDS']) . ',' .
              implode(',',$GLOBALS['REGISTRY_FIELDS']) . ',' .
              implode(',',$GLOBALS['REGISTRY_OVERRIDE_FIELDS']) . ',' .
              'd.dataset_udi udi';

    $WHERE = build_where($filters);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[IDENTIFIED_FROM] $WHERE ORDER BY $order_by;");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_registered_datasets($dbh, $filters = array(), $order_by = 'registry_id') {
    $SELECT = 'SELECT ' . 
              implode(',',$GLOBALS['REGISTRY_FIELDS']) . ',' .
              implode(',',$GLOBALS['DIF_FIELDS']) . ',' .
              implode(',',$GLOBALS['REGISTRY_OVERRIDE_FIELDS']) . ',' .
              "CASE WHEN r.dataset_udi IS NULL THEN substr(registry_id,1,16) ELSE r.dataset_udi END AS udi";

    $WHERE = build_where($filters,true);

    $stmt = $dbh->prepare("$SELECT $GLOBALS[REGISTERED_FROM] $WHERE ORDER BY $order_by;");
    $stmt->execute();
    return $stmt->fetchAll();
}

function build_where($filters,$registered = false) {
    $table = $registered ? 'r' : 'd';
    $WHERE = 'WHERE TRUE';

    foreach ($filters as $filter) {
        if (preg_match(FILTER_REG,$filter,$matches)) {
            switch (strtolower($matches[1])) {
                case 'registry_id':
                    $WHERE .= " AND registry_id " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'registry_ids':
                    $registry_ids = preg_split('/,/',$matches[3]);
                    $registry_id_filters = array();
                    foreach ($registry_ids as $registry_id) {
                        $registry_id_filters[] = "registry_id " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$registry_id%'";
                    }
                    if ($matches[2] == '!=') { $glue = ' AND '; }
                    else { $glue = ' OR '; }
                    $WHERE .= " AND (" . implode($glue,$registry_id_filters) . ")";
                    break;
                case 'udi':
                    $WHERE .= " AND $table.dataset_udi " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    break;
                case 'udis':
                    $udis = preg_split('/,/',$matches[3]);
                    $udiFilters = array();
                    foreach ($udis as $udi) {
                        $udiFilters[] = "$table.dataset_udi " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$udi'";
                    }
                    if ($matches[2] == '!=') { $glue = ' AND '; }
                    else { $glue = ' OR '; }
                    $WHERE .= " AND (" . implode($glue,$udiFilters) . ")";
                    break;
                case 'taskid':
                    $WHERE .= " AND task_uid $matches[2] $matches[3]";
                    break;
                case 'projectid':
                    $WHERE .= " AND project_id $matches[2] $matches[3]";
                    break;
                case 'projectids':
                    $projectIds = preg_split('/,/',$matches[3]);
                    $projects = array();
                    foreach ($projectIds as $projectId) {
                        $projects[] = "project_id $matches[2] $projectId";
                    }
                    if ($matches[2] == '!=') { $glue = ' AND '; }
                    else { $glue = ' OR '; }
                    $WHERE .= " AND (" . implode($glue,$projects) . ")";
                    break;
                case 'title':
                    if ($registered) {
                        $WHERE .= " AND dataset_title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    else {
                        $WHERE .= " AND title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    break;
                case 'abstract':
                    if ($registered) {
                        $WHERE .= " AND dataset_abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    else {
                        $WHERE .= " AND abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    break;
                case 'status':
                    $WHERE .= " AND status $matches[2] $matches[3]";
                    break;
                case 'dataset_download_status':
                    $WHERE .= " AND dataset_download_status $matches[2] '$matches[3]'";
                    break;
                case 'registered':
                    $WHERE .= " AND registry_id " . $GLOBALS['IS_MAP'][$matches[2]] . ' ' . $GLOBALS['NULL_MAP'][$matches[3]];
                    break;
                case 'filter':
                    $WHERE .= " AND (";
                    if ($registered) {
                        $WHERE .= "dataset_title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                        $WHERE .= " OR dataset_abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    else {
                        $WHERE .= "title " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                        $WHERE .= " OR abstract " . $GLOBALS['PGSQL_LIKE_MAP'][$matches[2]] . " '$matches[3]'";
                    }
                    $WHERE .= ")";
                    break;
            }
        }
    }

    return $WHERE;
}

?>
