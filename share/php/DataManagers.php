<?php

if (!function_exists('getDMsFromUser')) {
    function getDMsFromUser($uid)
    {
        require_once 'ResearchConsortia.php';
        require_once 'RIS.php';
        require_once 'db-utils.lib.php';
        # open a database connetion to RIS
        $RIS_DBH = OpenDB('RIS_RO');
        $dms = array();
        foreach (getRCsFromUser($uid) as $rc) {
            $dms = array_merge($dms, getDMsFromRC($RIS_DBH, $rc));
        }
        # close database connection
        $RIS_DBH = null;
        return $dms;
    }
}

if (!function_exists('getDMsFromUDI')) {
    function getDMsFromUDI($udi)
    {
        require_once 'ResearchConsortia.php';
        require_once 'RIS.php';
        require_once 'db-utils.lib.php';
        # open a database connetion to RIS
        $RIS_DBH = OpenDB('RIS_RO');
        $DMs = getDMsFromRC($RIS_DBH, getRCFromUDI($udi));
        # close database connection
        $RIS_DBH = null;
        return $DMs;
    }
}
