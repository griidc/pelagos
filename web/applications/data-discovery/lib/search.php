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

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function add_download_size(&$dataset) {
    if ($dataset['dataset_download_size']) {
        $dataset['filesize'] = bytes2filesize($dataset['dataset_download_size'],1);
    }
}

function add_project_info(&$dataset) {
    if ($dataset['project_id']) {
        $project = getProjectDetails(getDBH('RPIS'),array("projectId=$dataset[project_id]"));
        if (count($project) > 0) {
            $dataset['project'] = $project[0];
        }
        $pi = getPeopleDetails(getDBH('RPIS'),array("projectId=$dataset[project_id]",'roleId=1'));
        if (count($pi) > 0) {
            $dataset['pi'] = $pi[0];
        }
    }
}

function get_accepted_metadata($category) {
    if ($category == 'GoMRI') {
        $others = 'NOT';
    }
    else {
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

?>
