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

function cmp_registered_identified($a, $b)
{
    if ($a['registered_count'] == $b['registered_count']) {
        if ($a['identified_count'] == $b['identified_count']) {
            if (array_key_exists('PI',$a)) {
                return strcmp($a['PI']['LastName'],$b['PI']['LastName']);
            }
            return 0;
        }
        return ($a['identified_count'] > $b['identified_count']) ? -1 : 1;
    }
    return ($a['registered_count'] > $b['registered_count']) ? -1 : 1;
}

?>
