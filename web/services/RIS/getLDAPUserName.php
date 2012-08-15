<?php
function getFullNameFromDrupalUsername()
{
	global $user;
	$userId = $user->name;
	$ldap = ldap_connect("ldap://triton.tamucc.edu");
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
