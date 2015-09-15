<?php
// @codingStandardsIgnoreFile

require_once 'pdo.php';

function showPackageItems($conn,$username)
{
    $inpackage = (int) isInPackage($conn,$username);
    $return = array();
    if ($inpackage > 0)
    {
        $query = "SELECT udi from package where user_id = '$username' AND downloaded = FALSE";
        $rows = pdoDBQuery($conn,$query);
        foreach ($rows as $row)
        {
            array_push($return,$row['udi']);
        }
    }
    return $return;
}

function addToPackage($conn,$username,$udi)
{
    $now = date('c');
    $inpackage = (int) isInPackage($conn,$username,$udi);
    if ($inpackage <= 0)
    {
        $query = "INSERT INTO package (udi,user_id,added_timestamp) VALUES ('$udi', '$username', '$now')";
        $row = pdoDBQuery($conn,$query);
        if ($row != false AND isset($row[0][0]) AND $row[0][0] <> 0)
        {
            echo 'ERROR!'.var_dump($row);
            $inpackage = false;
        }
    }
    return $inpackage;
}

function removeFromPackage($conn,$username,$udi,$all=false)
{
    $inpackage = (int) isInPackage($conn,$username,$udi);
    if ($inpackage > 0)
    {
        if (!$all)
        {
            $query = "DELETE FROM package where user_id = '$username' AND udi='$udi' AND downloaded = FALSE";
        }
        else
        {
            $query = "DELETE FROM package where user_id = '$username' AND downloaded = FALSE";
        }
        $row = pdoDBQuery($conn,$query);
        if ($row != false AND isset($row[0][0]) AND $row[0][0] <> 0)
        {
            echo 'ERROR!';
            $inpackage = false;
        }
    }
    return $inpackage;
}

function emptyPackage($conn,$username)
{
    removeFromPackage($conn,$username,null,TRUE);
}

function isInPackage($conn,$username,$udi=null)
{
    if ($udi!=null)
    {
        $query = "SELECT COUNT(package_id) as count from package where user_id = '$username' AND udi='$udi' AND downloaded = FALSE";
    }
    else
    {
        $query = "SELECT COUNT(package_id) AS count from package where user_id = '$username' AND downloaded = FALSE";
    }
    $row = pdoDBQuery($conn,$query);
    if ($row != false)
    {
        return $row[0]['count'];
    }
}

function packageToJSON($conn,$username)
{
    $package = array();
    $package["count"] = isInPackage($conn,$username);
    $package["items"] = showPackageItems($conn,$username);
    return json_encode($package);
}

?>
