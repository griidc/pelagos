<?php
// @codingStandardsIgnoreFile

if (!function_exists('getRCsFromUser')) {
    function getRCsFromUser($userId)
    {
        require_once 'RIS.php';
        require_once 'DBUtils.php';
        require_once 'ldap.php';
        #consult LDAP for $userId -> $RIS_user_ID
        $risUserId = getEmployeeNumberFromUID($userId);
        # open a database connetion to RIS
        $RIS_DBH = openDB('RIS_RO');
        $project_ids = getRCsFromRISUser($RIS_DBH, $risUserId);
        # close database connection
        $RIS_DBH = null;
        return $project_ids;
    }
}
