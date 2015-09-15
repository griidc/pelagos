<?php
// @codingStandardsIgnoreFile

if (!function_exists('getDMsFromUser')) {
    function getDMsFromUser($uid)
    {
        require_once 'ResearchConsortia.php';
        require_once 'RIS.php';
        require_once 'DBUtils.php';
        # open a database connetion to RIS
        $RIS_DBH = openDB('RIS_RO');
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
        require_once 'DBUtils.php';
        require_once 'datasets.php';
        # open a database connetion to RIS
        $RIS_DBH = openDB('RIS_RO');
        $GOMRI_DBH = openDB('GOMRI_RO');
        $DMs = getDMsFromRC($RIS_DBH, getProjectIdFromUdi($GOMRI_DBH, $udi));
        # close database connections
        $RIS_DBH = null;
        $GOMRI_DBH = null;
        return $DMs;
    }
}
