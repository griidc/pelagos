<?php

function dbconnect()
{
    include 'dbConfig.php';
	//Connect to database
	$connString = "host=$dbserver port=$port dbname=$database user=$username password=$password";
 	$dbconn = pg_connect($connString)or die("Couldn't Connect : " . pg_last_error());
	//Check it
	if(!($dbconn))
	{
		//connection failed, exit with an error
		echo 'Database Connection Failed: ' . pg_errormessage($dbconn);#
		exit;
	}
	return $dbconn;
}

function dbexecute($query,$connection=null)
{
	if (isset($connection))
	{
		$returnds = pg_query($connection, $query);
	}
	else
	{
		$connection = dbconnect();
		$returnds = pg_query($connection, $query);
        if (!$returnds)
        {
            $returnds = pg_last_error($connection);
        }
        else
        {
            $returnds = pg_fetch_row($returnds);
        }
		pg_close($connection);
	}
	
	return $returnds;
}

?>