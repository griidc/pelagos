<?php

require_once('drupal.php');

function connectLDAP($ldaphost) {
    $ldapconnect = ldap_connect("ldap://$ldaphost");
    
    ldap_set_option($ldapconnect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconnect, LDAP_OPT_REFERRALS, 0);
    
    if (!ldap_bind($ldapconnect))
    {
        $dMessage = "Could not connect to LDAP. Please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.";
        drupal_set_message($dMessage,'error',false);
    }
    return $ldapconnect;
}

function getDNs($ldap,$basedn,$search) {
    $attributes = array('dn');
    $result = ldap_search($ldap, $basedn, $search, $attributes);
    if ($result === FALSE) { return array(); }
    $entries = ldap_get_entries($ldap, $result);
    if ($entries['count']>0) 
    { 
        return $entries;
    }
    else 
    { 
        return array(); 
    }
}

function isMember($ldap,$userDN,$groupDN) {
    $attributes = array('member');
    $result = ldap_read($ldap, "$groupDN", "(member=$userDN)", $attributes);
    if ($result === FALSE) 
    { 
        return FALSE; 
    }
    else
    {
        $entries = ldap_get_entries($ldap, $result);
        if ($entries['count'] > 0) { return TRUE; }else{ return FALSE; };
    }
    ldap_close($ldap);
}

?>
