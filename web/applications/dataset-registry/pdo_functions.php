<?php
//$dsn = 'pgsql:host=server.somewhere.edu;port=5432;dbname=name_of_database;user=username;password=Pas5W0rd!';

include_once '/usr/local/share/GRIIDC/php/drupal.php';

function pdoDBConnect($dsn) {
    try {
        $pdoconnection = new PDO($dsn);
        } catch (PDOException $e) {
        $dMessage = 'Connection failed: ' . $e->getMessage();
        drupal_set_message($dMessage,'error');
    }
    return $pdoconnection;
}

function pdoDBQuery($connection,$queryString) {

    $query = $connection->query($queryString);
    $i = 0;
    $queryReturn = false;
    if ($query != false)
    {
        foreach ($query as $query2) {
            $queryReturn[$i] = $query2;
            $i++;
        }
        if($i > 1) {
            return $queryReturn;
        } else {
            return $queryReturn[0];
        }
    }
    else
    {
        return $query;
    }
}

function pdoGetErrors($connection)
{
    $errlist = array();
    foreach ($connection->errorInfo() as $error)
    {
        $errmsg = 'ERRNUM:'.$error[0] . ',MESSAGE:'.$error[1];
        array_push($errlist,$errmsg);
        var_dump($error);
    }

    if (count($errlist>1))
    {
        return $errlist;
    }
    else
    {
        return $errlist[0];
    }
}
?>
