<?php

require_once 'lib/account.php';

if (count($argv) < 2) {
    print "Usage: php hash.php email_address\n";
    exit;
}

$RPIS = query_RPIS($argv[1]);

print $RPIS['hash'] . "\n";

?>
