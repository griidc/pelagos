<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

if (array_key_exists('uid', $_GET) and $_GET['uid'] != '') {
    $ldap = ldap_connect('ldap://triton.tamucc.edu');
    $result = ldap_search($ldap, 'ou=people,dc=griidc,dc=org', "(uid=$_GET[uid])", array('jpegPhoto'));
    $entries = ldap_get_entries($ldap, $result);
    $photo = null;
    if ($entries['count'] > 0) {
        $attrs =  ldap_get_attributes($ldap, ldap_first_entry($ldap, $result));
        if ($attrs['count'] > 0) {
            $photo = ldap_get_values_len($ldap, ldap_first_entry($ldap, $result), 'jpegPhoto');
        }
    }
    header('Content-type: image/jpeg');
    $num = 0;
    if (array_key_exists('num', $_GET) and $_GET['num'] != '' and intval($_GET['num'])) {
        $num = intval($_GET['num']);
    }
    if ($photo and array_key_exists($num, $photo) and $photo[$num]) {
        echo $photo[$num];
    } else {
        echo file_get_contents('/var/www/images/nopic.jpg');
    }
    exit;
} else {
    echo 'retrieve a user\'s photo from LDAP<br><br>
    Usage: photo?uid=&lt;uid&gt;[&num=&lt;photoNum&gt;]<br><br>
    Example: <a href="?uid=jdavis" target="_blank">photo?uid=jdavis<br><br>
    <img src="?uid=jdavis" /></a><br><br>
    Specifying a bad uid or a uid of a user without a photo will return a generic placeholder photo:<br><br>
    Example: <a href="?uid=baduid" target="_blank">photo?uid=baduid<br><br>
    <img src="?uid=baduid" /></a><br><br>
    You can also specify a photo number for alternate photos (the default is 0):<br><br>
    Example:<br><br>
    <table width=350>
        <tr>
            <td width=50% align=center>
                <a href="?uid=jdavis" target="_blank">photo?uid=jdavis<br><br>
                    <img src="?uid=jdavis" />
                </a>
            </td>
            <td width=50% align=center>
                <a href="?uid=jdavis&num=1" target="_blank">photo?uid=jdavis&num=1<br><br>
                    <img src="?uid=jdavis&num=1" />
                </a>
            </td>
        </tr>
    </table>
';
}
