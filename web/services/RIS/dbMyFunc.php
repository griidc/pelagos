<?php
// @codingStandardsIgnoreFile
// Module: dbMyConfig.php
// Author(s): Michael van den Eijnden
// Last Updated: 3 August 2012
// Parameters: None
// Returns: Common mySql functions
// Purpose: Execute a mySql query

/********************************************************
 * Function: executeMyQuery								*
 * Desc: Connects and executes mysql Query				*
 * Parameters:											*
 * $Query = sql query (select etc...)					*					
********************************************************/
function openConnection()
{
    require 'dbMyConfig.php';
    
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
    
    // Set client character set to UTF-8
    mysql_query('set character_set_client = utf8;');
    
    return $connection;
}

function executeMyQuery($query)
{
	// Execute Query
	$result = mysql_query($query);
	if (!$result) 
	{
		$message = 'Invalid query: ' . $query .'<p>' . mysql_error();
		throw new Exception ($message);
	}
	return $result;
}

function closeConnection($connection)
{
    mysql_close($connection);
}

?>
