<?php
// Module: dbMyConfig.php
// Author(s): Michael van den Eijnden
// Last Updated: 3 August 2012
// Parameters: None
// Returns: Common mySql functions
// Purpose: Execute a mySql query

require 'dbMyConfig.php';

/********************************************************
 * Function: executeMyQuery								*
 * Desc: Connects and executes mysql Query				*
 * Parameters:											*
 * $Query = sql query (select etc...)					*					
********************************************************/
function executeMyQuery($query)
{
	global $my_username;
	global $my_password;
	global $my_database;
	global $my_dbserver;
	
	// Opens a connection to a MySQL server
	$connection=mysql_connect ($my_dbserver, $my_username, $my_password);	
	
	if (!$connection) 
	{
		$message = 'Not connected : ' . mysql_error();
		throw new Exception ($message);
	}
	
	// Set the active MySQL database
	$dbSelected = mysql_select_db($my_database, $connection);
	if (!$dbSelected) 
	{
		$message= 'Can\'t use db: ' . mysql_error();
		throw new Exception ($message);
	}
	
	// Execute Query
	$result = mysql_query($query);
	if (!$result) 
	{
		$message = 'Invalid query: ' . $query .'<p>' . mysql_error();
		throw new Exception ($message);
	}
	mysql_close($connection);
	return $result;
}

?>