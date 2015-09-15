<?php
// @codingStandardsIgnoreFile

function dbconnect()
{
    include 'dbConfig.php';
	//Connect to database
	$connString = "host=$dbserver port=$port dbname=$database user=$username password=$password";
 	$dbconn = pg_connect($connString);
    
	if(!($dbconn))
	{
		//connection failed, exit with an error
        $dMessage= "A database error happened, please contact the administrator <a href=\"mailto:griidc@gomri.org?subject=DOI Error\">griidc@gomri.org</a>.";
        drupal_set_message($dMessage,'error',false);
		//echo 'Database Connection Failed: ' . pg_errormessage($dbconn);#
		//exit;
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