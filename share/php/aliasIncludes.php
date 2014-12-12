<?php

require_once 'dumpIncludesFile.php';

if (!isset($_SERVER['SCRIPT_NAME']) OR strpos($_SERVER['REQUEST_URI'],$_SERVER['SCRIPT_NAME']) === false) {
    $file = preg_replace('/^\/[^\/]+$/','',$_SERVER['REQUEST_URI']);
    $file = preg_replace('/^\/[^\/]+\//','',$file);
    $file = str_replace('//','/',$file);
}
else {
    $file = preg_replace('/^' . preg_replace('/\//','\/',$_SERVER['SCRIPT_NAME']) . '/','',$_SERVER['REQUEST_URI']);
    $file = preg_replace('/^\//','',$file);
}

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
