<?php
function getFullNameFromDrupalUsername()
{
	global $user;
    $config = parse_ini_file('/etc/opt/pelagos.ini', true);
    $config = array_merge($config, parse_ini_file($config['paths']['conf'].'/ldap.ini', true));
	$userId = $user->name;
    $ldap = ldap_connect('ldap://'.$config['ldap']['server']);
	$result = ldap_search($ldap, "ou=people,dc=griidc,dc=org", "(uid=$userId)", array('givenName','sn'));
	$entries = ldap_get_entries($ldap, $result);
	for ($i=0; $i<$entries['count']; $i++) 
	{
		$first = $entries[$i]['givenname'][0];
		$last = $entries[$i]['sn'][0];
		//echo "Are you... $first $last?<br>";
	}
	//$last = 'testLast';
	//$first = 'Firsttest';
	return array("firstName" => $first,"lastName" => $last);
}
?>
