<?php

require_once 'lib/constants.php';
require_once 'lib/account.php';
require_once 'config.php';

$GLOBALS['LDAP'] = ldap_connect('ldap://'.LDAP_HOST);

// Attempt to bind to LDAP.
if (!ldap_bind($GLOBALS['LDAP'], LDAP_BIND_DN, LDAP_BIND_PW)) {
    echo "Error binding to LDAP.\n";
    exit;
}

if (count($argv) < 2) {
    echo "Usage: php hash.php email_address\n";
    exit;
}

$person = get_ldap_user("mail=$argv[1]");

echo $person['hash'] . "\n";
