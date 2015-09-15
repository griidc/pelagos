<?php
// @codingStandardsIgnoreFile

function connectLDAP($ldaphost)
{
    $ldapconnect = ldap_connect("ldap://$ldaphost");

    ldap_set_option($ldapconnect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconnect, LDAP_OPT_REFERRALS, 0);

    if (!ldap_bind($ldapconnect)) {
        $dMessage = "Could not connect to LDAP. Please contact the administrator " .
                    "<a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.";
        throw new Exception($dMessage);
    }
    return $ldapconnect;
}

function getDNs($ldap, $basedn, $search)
{
    $attributes = array('dn');
    $result = ldap_search($ldap, $basedn, $search, $attributes);
    if ($result === false) {
        return array();
    }
    $entries = ldap_get_entries($ldap, $result);
    if ($entries['count']>0) {
        return $entries;
    } else {
        return array();
    }
}

function isMember($ldap, $userDN, $groupDN)
{
    $attributes = array('member');
    $result = ldap_read($ldap, "$groupDN", "(member=$userDN)", $attributes);
    if ($result === false) {
        return false;
    } else {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}

function memberHasApplicationRole($username, $applicationName, $applicationRole)
{
    $allowAccess=false;
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));
    $ldap = connectLDAP($config['ldap']['server']);
    // check for group membership
    $attributes = array('dn');
    $groupDN = "cn=$applicationRole,ou=$applicationName,ou=applications,dc=griidc,dc=org";
    $result = ldap_read($ldap, "$groupDN", "(member=uid=$username,ou=members,ou=people,dc=griidc,dc=org)", $attributes);
    if ($result === false) {
        return false;
    } else {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) {
            $allowAccess = true;
        }
    }
    // check for admin group membership
    $attributes = array('dn');
    $groupDN = "cn=administrators,ou=applications,dc=griidc,dc=org";
    $result = ldap_read($ldap, "$groupDN", "(member=uid=$username,ou=members,ou=people,dc=griidc,dc=org)", $attributes);
    if ($result === false) {
        return false;
    } else {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) {
            $allowAccess=true;
        }
    }
    ldap_unbind($ldap);
    return $allowAccess;
}

function getGroupMembers($group_dn)
{
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));
    // returns array of DN's having specified role DN
    $ldap = connectLDAP($config['ldap']['server']);
    $members = getAttributes($ldap, $group_dn, array('member'));
    ldap_unbind($ldap);
    unset($members['member']['count']); // redundant, best to use count() imho.
    return $members['member'];
}

function getAttributes($ldap, $dn, $attributes)
{
    $result = ldap_read($ldap, $dn, '(objectClass=*)', $attributes);
    if ($result === false) {
        return array();
    } else {
        $entry = ldap_first_entry($ldap, $result);
        $attrs = ldap_get_attributes($ldap, $entry);
        if ($attrs['count'] > 0) {
            return $attrs;
        } else {
            return array();
        }
    }
}

function getHomedir($user)
{
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));
    $ldap = connectLDAP($config['ldap']['server']);
    $dn_ary = getDNs($ldap, "ou=members,ou=people,dc=griidc,dc=org", "uid=$user");
    $attributes = getAttributes($ldap, $dn_ary[0]['dn'], array("homeDirectory"));
    if (array_key_exists("homeDirectory", $attributes) and
        count($attributes["homeDirectory"]) > 0 and
        isset($attributes["homeDirectory"][0])) {
        return $attributes["homeDirectory"][0];
    } else {
        return null;
    }
}

function userHasObjectClass($dn, $objectClass)
{
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));
    $ldap = connectLDAP($config['ldap']['server']);
    $attributes = getAttributes($ldap, $dn, array("objectClass"));
    if (isset($attributes["objectClass"][0])
        and (count($attributes["objectClass"]) > 0)
        and (in_array($objectClass, $attributes['objectClass']))) {
        return true;
    } else {
        return false;
    }
}

if (!function_exists('getEmployeeNumberFromUID')) {
    function getEmployeeNumberFromUID($gomri_userid)
    {
        $employeeNumber = null;
        $config = parse_ini_file('/etc/opt/pelagos.ini', true);
        $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));
        $ldap = connectLDAP($config['ldap']['server']);
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
