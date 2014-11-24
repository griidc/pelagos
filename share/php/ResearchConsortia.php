<?php

if (!function_exists('getRCFromUDI')) {
    function getRCFromUDI($udi)
    {
        # Precondition: Every registration has exactly 1 entry in the datasets (DIF) table.

        require_once 'db-utils.lib.php';
        $dbh = OpenDB("GOMRI_RW");
        $sql = "SELECT project_id from datasets WHERE dataset_udi = :udi";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":udi", $udi);
        $stmt->execute();
        $project_id = null;

        # Currently an UDI cannot be in more than one RC,
        # so the following will get a single value.
        if ($row = $stmt->fetch()) {
            $project_id = $row[0];
        }

        $dbms = null;
        unset($dbms);

        return $project_id;
    }
}

if (!function_exists('getRCsFromUser')) {
    function getRCsFromUser($griidc_ldap_uid)
    {
        require_once 'rpis.php';
        #consult LDAP for $griidc_ldap_uid -> $RIS_user_ID
        $RIS_user_id = getEmployeeNumberFromUID($griidc_ldap_uid);
        $project_ids = getRCsFromRISUser($RIS_user_id);
        return $project_ids;
    }
}

if (!function_exists('getEmployeeNumberFromUID')) {
    function getEmployeeNumberFromUID($gomri_userid)
    {
        require_once 'ldap.php';
        $employeeNumber = null;
        $ldap = connectLDAP($GLOBALS['ldap']['ldap']['server']);
        $baseDN = 'dc=griidc,dc=org';
        $userDN = getDNs($ldap, $baseDN, "uid=$gomri_userid");
        if (count($userDN) > 0) {
            $userDN = $userDN[0]['dn'];
            $attributes = getAttributes($ldap, $userDN, array('cn','givenName','employeeNumber'));
            if (array_key_exists("employeeNumber", $attributes)
                and count($attributes["employeeNumber"]) > 0
                and isset($attributes["employeeNumber"][0])) {
                $employeeNumber = $attributes['employeeNumber'][0];
            }
        }
        return $employeeNumber;
    }
}
