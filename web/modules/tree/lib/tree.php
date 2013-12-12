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

function getTree($app) {
    return array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
}

function getConfig($app) {
    $stash['config'] = $app->request()->get('config');
    if (isset($stash['config']) and file_exists($stash['config'])) {
        $config = parse_ini_file($stash['config'],true);
    }
    $config_attrs = array(
        'label',
        'theme',
        'dots',
        'icons',
        'animation',
        'max_depth',
        'expand_to_depth',
        'init_type',
        'init_open',
        'include_datasets',
        'afteropen',
        'onload',
        'show_other_sources',
        'yr1_folder_color',
        'yr1_folder_action',
        'yr1_color',
        'yr1_action',
        'rfp_color',
        'rfp_action',
        'project_color',
        'project_action',
        'task_color',
        'task_action',
        'dataset_color',
        'dataset_action',
        'researcher_color',
        'researcher_action',
        'institution_color',
        'institution_action',
        'other_sources_folder_color',
        'other_sources_folder_action',
        'other_sources_color',
        'other_sources_action'
    );
    $treename = $app->request()->get('treename');
    if (isset($treename)) {
        $stash['treename'] = $treename;
    }
    else {
        $treename = 'default';
    }
    foreach ($config_attrs as $attr) {
        if (isset($config["tree"][$attr])) {
            $stash[$attr] = $config["tree"][$attr];
        }
        elseif (isset($GLOBALS['config']['tree'][$attr])) {
            $stash[$attr] = $GLOBALS['config']['tree'][$attr];
        }
    }
    return $stash;
}

function getDatasetFilters($tree) {
    $filters = array();
    switch ($tree['include_datasets']) {
        case 'identified':
            $filters[] = 'status=2';
            break;
        case 'registered':
            $filters[] = 'registered!=null';
            break;
        case 'available':
            $filters[] = 'dataset_download_status=done';
            break;
        case 'unrestricted':
            $filters[] = 'access_status=None';
            break;
    }
    if (array_key_exists('geo_filter',$tree) and $tree['geo_filter']) {
        $filters[] = "geo_filter=$tree[geo_filter]";
    }
    return $filters;
}

?>
