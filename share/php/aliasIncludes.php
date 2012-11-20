<?php

require_once 'dumpIncludesFile.php';

$file = preg_replace('/^\/[^\/]+$/','',$_SERVER['REQUEST_URI']);
$file = preg_replace('/^\/[^\/]+\//','',$file);
$file = str_replace('//','/',$file);
$file = str_replace('../','',$file);
if (preg_match('/^includes\//',$file))
{
    dumpIncludesFile($file);
}

$URI = preg_split('/\?/',$_SERVER['REQUEST_URI']);

if (!preg_match('/\/$/',$URI[0]))
{
    $newLocation = "$URI[0]/";
    if (isset($URI[1]) and !empty($URI[1])) $newLocation .= "?$URI[1]";
    header("Location: $newLocation");
}

?>
