<?php
// @codingStandardsIgnoreFile

function getDBH($db)
{
    $dbh = new PDO(
        $GLOBALS['config'][$db.'_DB']['connstr'],
        $GLOBALS['config'][$db.'_DB']['username'],
        $GLOBALS['config'][$db.'_DB']['password'],
        array(PDO::ATTR_PERSISTENT => true)
    );

    if ($db == 'RPIS') {
        $stmt = $dbh->prepare('SET character_set_client = utf8;');
        $stmt->execute();
        $stmt = $dbh->prepare('SET character_set_results = utf8;');
        $stmt->execute();
    }

    return $dbh;
}

function gen_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,
        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

function add_download_size(&$dataset)
{
    if ($dataset['dataset_download_size']) {
        $dataset['filesize'] = bytes2filesize($dataset['dataset_download_size'], 1);
    }
}

function add_project_info(&$dataset, $RIS_DBH)
{
    if ($dataset['project_id']) {
        $project = getProjectDetails($RIS_DBH, array("projectId=$dataset[project_id]"));
        if (count($project) > 0) {
            $dataset['project'] = $project[0];
        }
    }
}

function get_accepted_metadata($category)
{
    if ($category == 'GoMRI') {
        $others = 'NOT';
    } else {
        $others = '';
    }
    $SQL = "SELECT dataset_udi,
            metadata_xml,
            CAST(
                xpath('/gmi:MI_Metadata/gmd:dateStamp[1]/gco:DateTime/text()',metadata_xml,
                    ARRAY[
                    ARRAY['gmi', 'http://www.isotc211.org/2005/gmi'],
                    ARRAY['gmd', 'http://www.isotc211.org/2005/gmd'],
                    ARRAY['gco', 'http://www.isotc211.org/2005/gco']
                    ]
                ) AS character varying
            ) AS last_modified_datetime,
            CAST(
                xpath('/gmi:MI_Metadata/gmd:dateStamp[1]/gco:Date/text()',metadata_xml,
                    ARRAY[
                    ARRAY['gmi', 'http://www.isotc211.org/2005/gmi'],
                    ARRAY['gmd', 'http://www.isotc211.org/2005/gmd'],
                    ARRAY['gco', 'http://www.isotc211.org/2005/gco']
                    ]
                ) AS character varying
            ) AS last_modified_date
            FROM registry_view
            JOIN metadata on metadata.registry_id = registry_view.registry_id
            WHERE metadata_status = 'Accepted' AND registry_view.registry_id $others LIKE '00%' ORDER BY dataset_udi";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($SQL);
    $data->execute();
    return $data->fetchAll();
}

function index($app)
{
    $env = $app->environment();
    drupal_add_js($GLOBALS['pelagos']['base_path'] . '/modules/tree/js/tree.js', array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/js/search.js", array('type'=>'external'));
    // drupal_add_js("$env[SCRIPT_NAME]/js/package.js",array('type'=>'external'));
    drupal_add_library('system', 'jquery.cookie');
    drupal_add_css("$env[SCRIPT_NAME]/css/search.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/scrollbars.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/datasets.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/downloads.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/dataset_details.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/dataset_download.css", array('type'=>'external'));
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/logins.css", array('type'=>'external'));
    if (array_key_exists('treePaneCollapsed', $GLOBALS['config']['DataDiscovery'])) {
        $stash['treePaneCollapsed'] = $GLOBALS['config']['DataDiscovery']['treePaneCollapsed'];
    } else {
        $stash['treePaneCollapsed'] = 0;
    }
    $stash['defaultFilter'] = $app->request()->get('filter');
    return $stash;
}

function getHashes($udi)
{
    $sql = "select fs_md5_hash, fs_sha1_hash, fs_sha256_hash from registry_view where
            dataset_udi = ?";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    $raw_data = $data->fetch();
    $md5    = '';
    $sha1   = '';
    $sha256 = '';
    if ($raw_data) {
        $md5    = $raw_data[0];
        $sha1   = $raw_data[1];
        $sha256 = $raw_data[2];
    }
    return "$md5|$sha1|$sha256";
}

function getApprovedMetadataUDIs()
{
    $hash = array();
    $sql = "select dataset_udi from registry_view where
            metadata_status = 'Accepted'";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute();
    while ($raw_data = $data->fetch()) {
        $hash[$raw_data[0]] = 1;
    }
    $dbms = null;
    return $hash;
}

function isMetadataApproved($udi)
{
    $sql = "select count(*) from registry_view where
            metadata_status = 'Accepted' and dataset_udi = ?;";
    $dbms = OpenDB("GOMRI_RO");
    $data = $dbms->prepare($sql);
    $data->execute(array($udi));
    while ($row = $data->fetch()) {
        $count = $row[0];
    }
    return ($count>0);
}

function canHazGridFTP($user, $udi, $filename)
{
    if (!isset($user->name)) {
        return 0;
    }
    $conf_path = $GLOBALS['config']['paths']['conf'];
    $GLOBALS['ldap'] = parse_ini_file("$conf_path/ldap.ini", true);
    $ldaphost = $GLOBALS['ldap']['ldap']['server'];
    $baseDN = $GLOBALS['ldap']['ldap']['base_dn'];

    $ldap = connectLDAP($ldaphost);
    $dns = getDNs($ldap, $baseDN, "uid=$user->name");
    $dn = $dns[0]['dn'];

    $posix = userHasObjectClass($dn, "posixAccount");
    $homedir = getHomeDir($user->name);

    clearstatcache(); # needed because stat operations like file_exists() cache file state tests

    $all_ok = 0;
    if ($posix
        and isset($homedir)
        and $homedir != null
        and is_writable($homedir."/download/")
        and (is_writable($homedir."/download/$udi") or !file_exists($homedir."/download/$udi"))
    ) {
        $all_ok = 1;
    }
    return $all_ok;
}
