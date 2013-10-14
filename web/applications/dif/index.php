<?php

require_once '/usr/local/share/GRIIDC/php/ldap.php';
require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/aliasIncludes.php';
require_once '/usr/local/share/GRIIDC/php/dif-registry.php';
require_once 'lib/functions.php';

$isGroupAdmin = false;

$GLOBALS['DIF'] = true;

if (!file_exists('config.php')) {
    echo 'Error: config.php is missing. Please see config.php.example for an example config file.';
    exit;
}
require_once 'config.php';

$uid = getUID();
if (!isset($uid)) {
    $currentpage = urlencode(preg_replace('/^\//','',$_SERVER['REQUEST_URI']));
    drupal_set_message("You must be logged in to access the Dataset Information Form.<p><a href='/cas?destination=$currentpage' style='font-weight:bold;'>Log In</a></p>",'error');
}
else {
?>

<table style="margin: 0; padding: 20; border: 5; outline: 0; font-size: 100%; vertical-align: top; width:100%;">
    <tr>
        <td style="vertical-align: top; width:60%" >
            <?php require_once 'dif.php'; ?>
        </td>
        <td>&nbsp;&nbsp;</td>
        <td style="vertical-align: top; width:40%; height:2500px;" >
            <div style="position:relative;">
                <div style="position:absolute; left:0px; right:10px;">
                    <?php require_once '/usr/local/share/GRIIDC/php/sidebar.php'; ?>
                </div>
            </div>
        </td>
    </tr>
</table>

<?php
}
?>
